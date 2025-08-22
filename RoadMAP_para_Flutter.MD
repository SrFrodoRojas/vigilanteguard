1) Funcionalidad actual (casos de uso)

Entradas

Registrar entrada vehículo (placa, marca, color, tipo) + chofer + acompañantes.

Registrar entrada peatón (documento + nombre).

Observación de entrada opcional.

Se guarda sucursal (branch_id) y usuario que registró (user_id).

Listados

Listado principal con dos secciones: Vehículos y A pie, cada una con filtros independientes y paginación.

Activos: todo lo que está “dentro” (personas con exit_at NULL), mostrando si el vehículo salió (vehicle_out) o no.

Salidas

Buscar (por placa o documento) un acceso activo.

Registrar salida de personas seleccionadas; opcionalmente marcar “el vehículo sale ahora” y quién conduce al salir.

Observación de salida opcional.

Detalle (show) de un acceso: datos generales + lista de personas (entrada/salida) + estado.

Dashboard

KPIs del día: totales, activos ahora, desglose veh/a pie, entradas por hora, y, si sos admin, resumen por sucursal.

Reportes

Rango de fechas (from/to): totales, vehículos, peatones, promedio de permanencia y detalle paginado.

Sucursales

ABM: nombre, ubicación, encargado (user admin), color opcional (badge), WhatsApp si tiene teléfono.

Usuarios

Admins y Guardias, con filtros por nombre/email/teléfono, estado, sucursal; edición/alta/baja.

Teléfono normalizado, link a WhatsApp, protección del último admin.

Lookup de personas

/personas/lookup?document=… para autocompletar nombre/género.

2) Estructura de datos (tablas clave)
users

id, name, email, password, is_active (tinyint/bool), branch_id (nullable), phone (nullable)

Relaciones: belongsTo branch, hasOne managedBranch, roles/permissions vía spatie.

branches

id, name, location, manager_id (nullable), color (opcional, si lo agregaste)

Relaciones: manager (User), users (guardias), accesses.

accesses

id, type ('vehicle'|'pedestrian'), plate (nullable), marca_vehiculo, color_vehiculo, tipo_vehiculo, full_name, document, entry_at, entry_note (nullable), vehicle_exit_at (nullable), exit_at (nullable), exit_note (nullable), vehicle_exit_driver_id (nullable), user_id, branch_id

Estados derivados:

inside_count = personas con exit_at IS NULL (en access_people).

vehicle_out = vehicle_exit_at no es null.

“Cerrado” = exit_at no es null.

“Pendiente” = exit_at null y inside_count == 0.

access_people

id, access_id, full_name, document, role ('driver'|'passenger'|'pedestrian'), is_driver (bool), gender (nullable), entry_at, exit_at (nullable)

people

Maestro opcional para lookup: id, full_name, document (unique), gender (nullable)

Spatie (permisos/roles)

roles, permissions, model_has_roles, model_has_permissions, role_has_permissions

TZ usado: America/Asuncion
Auth web: Laravel session + Spatie roles.
Filtros por sucursal: si NO sos admin, todo va acotado a user.branch_id.

3) Rutas web actuales (Laravel)
# Protegidas por ['auth','active'] + permisos Spatie
GET   /                 -> redirige según permisos
GET   /resumen          -> DashboardController@summary          (name: dashboard.summary)

# Access
GET   /accesos          -> AccessController@index               (name: access.index)
GET   /accesos/activos  -> AccessController@active              (name: access.active)
GET   /accesos/crear    -> AccessController@create              (name: access.create)
POST  /accesos          -> AccessController@store               (name: access.store)
GET   /accesos/{access} -> AccessController@show                (name: access.show)

# Salidas
GET   /salida           -> AccessController@exitForm            (name: access.exit.form)
GET|POST /salida/buscar -> AccessController@search              (name: access.search)
POST  /salida/registrar/{access} -> AccessController@registerExit (name: access.registerExit)

# Reportes
GET   /reportes         -> ReportsController@index              (name: reports.index)

# Personas
GET   /personas/lookup  -> PeopleController@lookup              (name: people.lookup)

# Admin
GET   /branches         -> BranchController@index               (name: branches.index)
GET   /branches/create  -> BranchController@create              (name: branches.create)
POST  /branches         -> BranchController@store               (name: branches.store)
GET   /branches/{id}/edit -> BranchController@edit              (name: branches.edit)
PUT   /branches/{id}    -> BranchController@update              (name: branches.update)
DELETE /branches/{id}   -> BranchController@destroy             (name: branches.destroy)

GET   /admin/users      -> UserController@index                 (name: admin.users.index)
GET   /admin/users/create -> UserController@create              (name: admin.users.create)
POST  /admin/users      -> UserController@store                 (name: admin.users.store)
GET   /admin/users/{id}/edit -> UserController@edit            (name: admin.users.edit)
PUT   /admin/users/{id} -> UserController@update                (name: admin.users.update)
DELETE /admin/users/{id} -> UserController@destroy              (name: admin.users.destroy)


Middleware de permisos (orientativo):

access.view, access.view.active, access.enter, access.exit, access.show, reports.view, users.manage, branches.manage, roles.manage.

4) Vistas actuales (Blade) y datos que esperan

access/index.blade.php:
Vars: $vehicles (Paginator), $pedestrians (Paginator), $branches (para selects), $isAdmin (bool).
Query params (duplicados por sección):

Vehículos: q_veh, branch_veh, status_veh[in|closed|pending], from_veh, to_veh, veh_page

Peatones: q_ped, branch_ped, status_ped, from_ped, to_ped, ped_page

access/show.blade.php:
Vars: $access (+ people), $insideCount, $driverEntry, $driverExit.

access/create.blade.php:
Vars: nada especial (solo old inputs).
POST: type (vehicle|pedestrian), placa/marca/color/tipo (si vehicle), chofer (doc/name/gender), passengers[] (opc), entry_note.

access/exit.blade.php:
GET: plate o document -> muestra acceso y personas dentro.
POST: vehicle_exit (bool), vehicle_exit_driver_id (opc), people_exit[], exit_note.

access/active.blade.php:
Vars: $accesses (paginado), cada fila con insideCount y vehicleOut.

dashboard/summary.blade.php:
Vars: totalToday, activeNow, todayVehicle, todayPedestrian, hours[] (0..23), hourCounts[], branchesSummary (solo admin).
GET opcional: branch_id (solo admin).

reportes/index.blade.php:
GET: from (Y-m-d), to (Y-m-d)
Vars: total, vehiculos, peatones, promedioMin, accesses (paginado).

admin/branches/{index,create,edit}.blade.php
Listado con DataTables, ABM, select de encargado y color opcional.

admin/users/{index,create,edit}.blade.php
Filtros y paginación para Admins/Guardias, teléfono con enlace WhatsApp; edición/alta/baja.

vendor/adminlte/master.blade.php + page.blade.php + partials/footer/footer.blade.php:
Layout (PWA, SW, footer, plugins, etc.).

5) API REST equivalente (para montar en Express)

Auth: te conviene JWT en Express. Como comparten DB, validás el users.password (bcrypt) y devolvés un JWT con sub: user.id, roles: [...].
Permisos: replicá checks leyendo model_has_roles (role.name) y, si querés, permissions.
TZ: siempre UTC en DB; en respuestas podés adjuntar tz: 'America/Asuncion' o devolver ISO y el cliente formatea.

Auth
POST /api/login
Body: { email, password }
Res:  { token, user: { id,name,email,branch_id,roles:["admin"| "guardia"], phone } }

POST /api/logout
(Cliente borra token; server opcionalmente lista negra)

Personas (lookup)
GET /api/people/lookup?document=1234567
Res: { found: true, full_name, gender } | { found:false }

Accesos (listado general con filtros)
GET /api/accesses
Query:
  type=vehicle|pedestrian (opcional; si no viene, devuelve ambos)
  q=string (busca en nombre/documento/placa)
  branch_id=ID (admin puede filtrar; guardia ignora y usa la suya)
  status=inside|closed|pending
  from=YYYY-MM-DD  to=YYYY-MM-DD
  page=1  per_page=20
Res:
{
  data: [
    { id, type, plate, full_name, document,
      entry_at, exit_at, vehicle_exit_at,
      inside_count, vehicle_out, branch: {id,name,color}, user:{id,name}
    }, ...
  ],
  meta: { current_page, last_page, per_page, total }
}

Cálculos SQL útiles (Express)

inside_count por acceso:

SELECT access_id, COUNT(*) AS inside
FROM access_people
WHERE exit_at IS NULL AND access_id IN (...)
GROUP BY access_id;


vehicle_out: vehicle_exit_at IS NOT NULL

Accesos activos
GET /api/accesses/active
Query: branch_id (admin) | page/per_page
Res: igual que /api/accesses pero solo activos (exit_at IS NULL o inside_count>0)

Crear entrada
POST /api/accesses
Body (vehículo):
{
  type: "vehicle",
  branch_id, plate, marca_vehiculo?, color_vehiculo?, tipo_vehiculo?,
  entry_note?,
  driver: { document, full_name, gender? },
  passengers: [{ document, full_name, gender? }, ...]  // opcional
}

Body (peatón):
{
  type: "pedestrian",
  branch_id,
  person: { document, full_name, gender? },
  entry_note?
}

Res: { id, ... } (registro completo)


Reglas: validar doc/nombre, placa en mayúsculas, etc.
user_id = usuario autenticado (del JWT).

Buscar para salida
GET /api/accesses/search?plate=ABC123 | document=1234567
Res: { data: [ accesos activos que matchean ], ... } o { data: [] }

Registrar salida
POST /api/accesses/:id/exit
Body:
{
  vehicle_exit: true|false,
  vehicle_exit_driver_id: number|null, // si vehicle_exit true
  people_exit: [person_id, ...],       // ids de access_people que salen ahora
  exit_note?: string
}
Res: 200 OK (y devolvés el acceso actualizado)

Detalle de acceso
GET /api/accesses/:id
Res:
{
  id, type, branch:{id,name,color}, user:{id,name},
  plate, marca_vehiculo, color_vehiculo, tipo_vehiculo,
  entry_at, entry_note, vehicle_exit_at, exit_at, exit_note,
  inside_count, vehicle_out,
  people: [
    { id, full_name, document, role, is_driver, gender, entry_at, exit_at }
  ]
}

Dashboard
GET /api/dashboard/summary?branch_id=  // admin opcional
Res:
{
  totals: { today: number, active_now: number, vehicle: number, pedestrian: number },
  entries_per_hour: { labels:[0..23], counts:[...] },
  branches_summary?: [
    { id, name, color?, total_today, today_vehicle, today_pedestrian, active_now }
  ]
}

Queries útiles

Entradas por hora (del día):

SELECT HOUR(CONVERT_TZ(entry_at,'UTC','America/Asuncion')) h, COUNT(*) c
FROM accesses
WHERE entry_at BETWEEN :from AND :to
  AND (branch_id = :branchFilter) -- si aplica
GROUP BY h;

Reportes
GET /api/reports?from=YYYY-MM-DD&to=YYYY-MM-DD&branch_id=
Res:
{
  totals: { total, vehicles, pedestrians, avg_minutes },
  list: { data:[ ... mismos campos que /api/accesses ... ], meta:{...} }
}

Sucursales (admin)
GET    /api/branches
POST   /api/branches         { name, location, manager_id?, color? }
PUT    /api/branches/:id     { name?, location?, manager_id?, color? }
DELETE /api/branches/:id

Usuarios (admin)
GET    /api/users?role=admin|guardia&search=&branch_id=&status=active|inactive&page=
POST   /api/users            { name,email,password,role,branch_id?,is_active?,phone? }
GET    /api/users/:id
PUT    /api/users/:id        { name?,email?,password?,role?,branch_id?,is_active?,phone? }
DELETE /api/users/:id

6) Seguridad (Express)

JWT firmado (HS256). Middleware authJWT.

RBAC: requireRole('admin') para endpoints admin; requirePermission si querés granularidad.

Scope por sucursal (guardia): inyectar req.user.branch_id y forzar filtro en queries.

Rate Limit / CORS: rate limit básico y CORS solo para tu app Flutter (o abierto si lo necesitás).

Fechas: almacenar UTC; cliente formatea al tz.

7) Paginación y metadatos (formato)

Usá un formato consistente:

{
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 88
  }
}


Query: ?page=1&per_page=20

8) Campos derivados (para la UI)

status (server-side o client-side):

inside_count > 0 → "inside"

exit_at != null → "closed"

else → "pending"

vehicle_out = vehicle_exit_at != null

9) Notas para Flutter (consumo rápido)

Al iniciar: POST /api/login → guarda token + roles.

Home: GET /api/dashboard/summary

Activos: GET /api/accesses/active?page=1

Crear: POST /api/accesses (según tipo)

Salida:

Buscar: GET /api/accesses/search?plate=... o ?document=...

Registrar: POST /api/accesses/:id/exit

Reportes: GET /api/reports?from=&to=

Sucursales/Usuarios (solo admin) según necesidad.
