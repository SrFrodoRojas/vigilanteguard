# Vigilante Guard

Sistema de control y registro para **accesos de vehículos/personas**, **rondas de patrullaje con checkpoints (QR + GPS)** y **reportería**, con multi-sucursal y RBAC.

## 🧰 Stack

- **Laravel** 12.x (PHP 8.3)
- **PHP** 8.3+
- **MySQL/MariaDB**
- **Spatie/laravel-permission** (RBAC)
- Blade + Bootstrap/AdminLTE (UI)
- Jobs/Queues opcionales (pendiente según despliegue)

---

## ✨ Funcionalidades

- **Accesos** (vehículo/peatón):
  - Registro de entrada, ocupantes, salida completa y salida individual.
  - Antiduplicados: evita doble entrada por **placa** o **documento** activos.
  - Denormalizados en `accesses`: `full_name`, `document`, `people_count` (NOT NULL).
- **Patrullas**:
  - **Checkpoints** con QR + geocerca (lat/lng/radio) y short code.
  - **Scan** con GPS (opcional/obligatorio según ruta), verificación por radio efectivo.
  - Detección de señales sospechosas (*speed/jump*) por saltos o velocidad irreal.
- **Sucursales**:
  - Manager opcional por sucursal, color hex.
- **Usuarios & Roles**:
  - Activación/desactivación, avatar, asignación de roles.
- **Reportes**:
  - Filtros por fecha/sucursal/tipo/estado y KPIs rápidos.

---

## 🚀 Puesta en marcha (local)

1) Clonar e instalar dependencias
```bash
composer install
cp .env.example .env
php artisan key:generate
```

2) Configurar **.env** (DB, APP_URL, etc.), luego:
```bash
php artisan migrate
php artisan storage:link
php artisan serve
```

> **Notas**
> - Si usás colas: `php artisan queue:work` (opcional).
> - Si tu entorno no crea sesiones, verificá permisos en `storage/` (Linux).

---

## 🗃️ Modelo de datos (resumen)

- **users**: `is_active` es **varchar NOT NULL** → se usa `'1'/'0'` en código (mapeado a boolean con casts).
- **branches**: `name`, `location`, `color(#RRGGBB)`, `manager_id` (FK->users).
- **accesses** (denormalizados):
  - `type` (`vehicle|pedestrian`), `plate?`, `entry_at`, `exit_at?`
  - **NOT NULL**: `full_name`, `document`
  - `people_count` (string), `vehicle_exit_driver_id?`
  - FK: `user_id`, `branch_id`
- **access_people** (ocupantes):
  - `full_name`, `document`, `role` (`driver|passenger|pedestrian`), `is_driver`
  - `entry_at`, `exit_at?`, FK: `access_id`
- **patrol_routes**: por sucursal, `qr_required` (1/0), `min_radius_m`
- **checkpoints**: `latitude/longitude/radius_m`, `qr_token`(UUID), `short_code`
- **patrol_assignments**: `guard_id`, `patrol_route_id`, `scheduled_start/end`, `status`
- **checkpoint_scans**:
  - `scanned_at`, `lat/lng?`, `distance_m?`, `accuracy_m?`, `verified(0/1)`
  - `speed_mps?`, `jump_m?`, `suspect(0/1)`, `suspect_reason?`
  - **ÚNICO**: `(patrol_assignment_id, checkpoint_id)`

---

## 🔐 Seguridad

- Middleware: `web`, `auth`, `active` y permisos vía **Spatie** (ej.: `permission:access.enter`).
- Rutas **web** → **CSRF requerido** para POST/PUT/DELETE (UI).  
  *(Si vas a testear con Postman, debes iniciar sesión y enviar `X-CSRF-TOKEN`; no se documenta aquí para no alargar).*

---

## 🧭 Rutas clave (web)

> Según `php artisan route:list` (nombre → controlador@método):

Accesos:
- `GET  accesos` → `access.index` → `AccessController@index`
- `GET  accesos/activos` → `access.active` → `AccessController@active` (alias de `activos`)
- `GET  accesos/crear` → `access.create` → `AccessController@create`
- `POST accesos` → `access.store` → `AccessController@store`
- `GET  accesos/{access}` → `access.show` → `AccessController@show`
- `GET  salida` → `access.exit.form` → `AccessController@exitForm` (alias de `exitIndex`)
- `GET|POST salida/buscar` → `access.search` → `AccessController@search`
- `POST salida/registrar/{access}` → `access.registerExit` → `AccessController@registerExit`
- (Opcional) `POST access-people/{person}/exit` → `accesses.people.exit` → `AccessController@registerExitPerson`

Patrullas (nombres pueden variar según tu archivo `routes/web.php`):
- `GET  patrol/scan` → pantalla de escáner (`ScanController@showScanner`)
- `POST patrol/scan` → registrar escaneo (`ScanController@store`)

Admin:
- `Admin\UserController` (crear/editar usuario, roles, avatar)
- `Admin\BranchController` (crear/editar sucursal)
- `Admin\PatrolAssignmentController`, `Admin\PatrolDashboardController` (según UI)

Reportes:
- `ReportsController@index` → vista `reportes.index` con filtros y KPIs

---

## 🧱 Controladores endurecidos

### AccessController (principal)
- **Validación** inline (entrada, ocupante, salida).
- **Transacciones** en escrituras.
- **Antiduplicados** con `lockForUpdate()`:
  - Placa: no permite otra entrada con la misma **sin salida**.
  - Documento: no permite que una persona esté “dentro” en dos accesos.
- **Denormalizados**: `people_count`, `full_name`, `document` se actualizan coherentemente.
- **Fix** para `NOT NULL` en `accesses.full_name/document`: al crear acceso se toma del **primer ocupante** o de campos del formulario.

### Patrol\\ScanController
- **GPS requerido** si la ruta (`patrol_routes.qr_required`) lo pide.
- **Radio efectivo** = `max(checkpoint.radius_m, route.min_radius_m)`.
- **Política de precisión (accuracy)** configurable en el método:
  - `accuracyMax` (por defecto 50 m).
  - `modoEstrictoAccuracy`:
    - `false` → guarda como **no verificado** + *warn* si `accuracy > accuracyMax`.
    - `true` → **bloquea** y no guarda si `accuracy > accuracyMax`.
- **Modo estricto de radio** (`modoEstrictoradio`):
  - `false` (default) → guarda fuera de radio como **no verificado**.
  - `true` → **bloquea** fuera de radio.
- **Anti-fraude**: marca `suspect` / `suspect_reason` si:
  - `speed_mps > 15` (~54 km/h) **o**
  - `jump_m > 150` en `< 10s`.
- **Índice único** `(patrol_assignment_id, checkpoint_id)`:
  - manejo de carrera con `DB::transaction` + captura de error 1062 (duplicado).

### Admin\\UserController
- `store()/update()` con validación, hash de password, `'1'/'0'` para `is_active`,
- Avatar opcional con `storage:link`, reemplazo y eliminación segura,
- Roles con `syncRoles`.

### Admin\\BranchController
- `store()/update()` con validación y normalización de color **#RRGGBB**,
- `manager_id` opcional (FK a users).

### ReportsController@index
- Filtros validados (`from/to`, sucursal, tipo, estado, búsqueda),
- Scope por sucursal para no-admins,
- **KPIs** eficientes (aprovechan índices).

---

## 📈 Índices de rendimiento (migración aplicada)

Archivo: `database/migrations/2025_08_22_190004_add_indexes_for_accesses_and_access_people.php`

Crea índices útiles para búsquedas frecuentes:

- **accesses**
  - `idx_accesses_plate`
  - `idx_accesses_entry_at`
  - `idx_accesses_exit_at`
  - `idx_accesses_plate_exit_at` (compuesto)
- **access_people**
  - `idx_access_people_document`
  - `idx_access_people_exit_at`
  - `idx_access_people_document_exit_at` (compuesto)

---

## 🧪 Reglas de negocio (resumen práctico)

- **Crear acceso**
  - `type` requerido (`vehicle|pedestrian`).
  - Si `vehicle` → `plate` requerido.
  - **No** permite placas activas duplicadas.
  - Si no hay `people[]`, puede venir `full_name/document` top-level (form UI).
- **Agregar ocupante**
  - **No** permite que un `document` esté “dentro” en 2 accesos.
- **Salida de acceso**
  - Cierra todos los ocupantes “dentro”.
  - Opcional `driver_person_id` para `vehicle_exit_driver_id`.
- **Scan checkpoint**
  - Si la ruta exige GPS: requiere `lat/lng`.
  - `verified=1` si `distance <= radio efectivo` **y** precisión aceptable.
  - Caso contrario se guarda `verified=0` (o se **bloquea** si activás modo estricto).
  - Señales sospechosas: `suspect=1` si velocidad/salto anómalo.

---

## 🧭 Convenciones de desarrollo

- **Transacciones** en todas las escrituras que toquen múltiples filas (`DB::transaction`).
- **Validación** en controlador o FormRequest (según preferencia; hoy: controlador).
- **Ramas**: `feat/*`, `chore/*`, `fix/*`.  
  Commits claros, ej.:  
  - `access: validación/transacciones/anti-duplicados`  
  - `patrol: scan verificado por radio y accuracy`
- **CSRF**: para rutas web, siempre usar formulario/UI (o sesión + token si API client).

---

## 🔧 Configuración rápida (puntos “tuneables”)

> Por ahora viven dentro de los métodos; luego podemos moverlos a `config/patrol.php`.

**Patrol\\ScanController@store**
```php
$modoEstrictoradio    = false; // true => no guarda fuera de radio
$modoEstrictoAccuracy = false; // true => no guarda si accuracy > $accuracyMax
$accuracyMax          = 50;    // metros
```

- Si activás estricto, bloquea con mensaje y **no** crea el scan.
- En modo no estricto, crea el scan pero queda **no verificado** y con *warn*.

---

## 📌 Roadmap corto

- Mover toggles de Scan a `config/` + .env.
- Export/CSV en reportes.
- Tests de integración (Pest/PhpUnit) para critical flows.
- Endurecer `Admin\\PatrolAssignmentController` (transiciones de estado).

---

## 👤 Créditos

- Proyecto: **Vigilante Guard**
- Autores: equipo interno + colaboraciones
- Licencia: privada (ajustar si corresponde)
