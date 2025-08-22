# Vigilante — Control de Accesos (Laravel + AdminLTE)

Sistema para registrar **entradas y salidas** de personas y vehículos, con control de ocupantes, reportes y roles.

## Características

- **Entradas**:
  - Vehículo: placa, chofer, acompañantes opcionales, marca/color/tipo, observación.
  - A pie: persona individual con observación.
  - Verificación en tiempo real: **impide** que una **placa** o **documento** ya dentro vuelva a ingresar.
  - Maestro de personas: si se ingresa un documento conocido, **autocompleta el nombre**.

- **Salidas**:
  - Búsqueda por **placa** o **documento**.
  - Peatón individual: con un clic queda cerrada.
  - Vehículo: permite elegir **quién conduce** al salir (chofer u acompañante).
  - Cierra el acceso al quedar **sin ocupantes**.

- **Listados/Reportes**:
  - Accesos, activos, reportes por rango con **KPIs**.
  - Filas **clicables**:
    - Si aún hay dentro → **Registrar salida**.
    - Si está cerrado → **Detalle** del acceso.
  - Botón de **Salida** directo cuando corresponde.

- **Detalle** de acceso:
  - Datos del vehículo y ocupantes.
  - Quién entró conduciendo y quién **salió conduciendo**.
  - Observaciones.

- **UI**:
  - AdminLTE 3 con **Chart.js** v4.
  - Soporte **responsive** (móvil/desktop).

- **Zona horaria**: `America/Asuncion`.

## Requisitos

- PHP 8.2+
- MySQL/MariaDB
- Composer
- Node (opcional, si usas assets propios)

## Instalación rápida

```bash
git clone <repo>
cd vigilante
cp .env.example .env
# Edita DB_*, APP_URL, TIMEZONE=America/Asuncion, APP_LOCALE=es
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed  # si tienes seeders
php artisan serve


Modelos
Access (accesos)

type (vehicle|pedestrian), plate, vehicle_make|color|type

entry_at, exit_at, entry_note, exit_note

vehicle_exit_at, vehicle_exit_driver_id (quién condujo al salir)

user_id (quien registró)

AccessPerson (ocupantes por acceso)

access_id, full_name, document, gender

role (driver|passenger|pedestrian), is_driver

entry_at, exit_at

Person (maestro de personas)

full_name, document (único), gender

User (operadores del sistema, con roles/permisos)

Controladores
AccessController

index() — Listado general (botón salida/detalle)

active() — Solo activos (dentro)

create() — Form entrada

store() — Registra entrada (valida duplicados y “ya dentro” por placa/doc)

exitForm() — Búsqueda de salida

search() — Busca activo por placa/documento

registerExit() — Registra salida; conductor al salir

show() — Detalle del acceso

ReportsController

index() — KPIs + listado por rango (validación de fechas)

PeopleController

lookup() — Autocompleta por documento (JSON)

Vistas (Blade)
accesos/index.blade.php — listado, filas clicables

accesos/activos.blade.php — activos

accesos/create.blade.php — entrada (vehículo/a pie)

accesos/exit.blade.php — salida (conductor al salir)

accesos/show.blade.php — detalle

reportes/index.blade.php — filtros, KPIs y tabla clicable

dashboard/summary.blade.php — resumen (opcional)

Rutas principales
GET / → listado general (home)

GET /accesos → listado

GET /accesos/activos → activos

GET /accesos/crear → form entrada

POST /accesos → guardar entrada

GET /salida → form salida

GET|POST /salida/buscar → buscar activo por placa/documento

POST /salida/registrar/{access} → registrar salida

GET /accesos/{access} → detalle

GET /reportes → reportes

GET /personas/lookup?document=xxxx → JSON para autocompletar

Tecnologías
Laravel 12

AdminLTE 3

Chart.js 4 (UMD)

Spatie/laravel-permission (roles y permisos)

MySQL/MariaDB

Notas
Zona horaria: config/app.php → 'timezone' => 'America/Asuncion'.

Validación de rangos en reportes: “Hasta” ≥ “Desde”.

Evita duplicados: documento dentro del mismo acceso y documento/placa ya dentro.

No olvidar nuca para el modulo de patrullaje
* * * * * cd /ruta/a/tu/proyecto && php artisan schedule:run >> /dev/null 2>&1

