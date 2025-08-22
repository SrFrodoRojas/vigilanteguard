<?php
namespace App\Http\Controllers;

use App\Models\Access;
use App\Models\AccessPerson;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AccessController extends Controller
{
    /*
     |------------------------------------------------------------------
     |  LISTADOS
     |------------------------------------------------------------------
     */

    // Listado general
    public function index()
    {
        $user    = auth()->user();
        $isAdmin = $user->hasRole('admin');

                                            // ====== Parámetros Vehículos ======
        $qVeh      = request('q_veh');      // búsqueda por nombre/email/doc/placa
        $branchVeh = request('branch_veh'); // sucursal para admin
        $statusVeh = request('status_veh'); // inside|closed|pending
        $fromVeh   = request('from_veh');   // YYYY-MM-DD
        $toVeh     = request('to_veh');     // YYYY-MM-DD

        // ====== Parámetros Peatones ======
        $qPed      = request('q_ped');
        $branchPed = request('branch_ped');
        $statusPed = request('status_ped');
        $fromPed   = request('from_ped');
        $toPed     = request('to_ped');

        // Helper para rango de fechas (entry_at)
        $applyDateRange = function ($q, $from, $to) {
            if ($from) {
                $q->where('entry_at', '>=', \Carbon\Carbon::parse($from)->startOfDay());
            }
            if ($to) {
                $q->where('entry_at', '<=', \Carbon\Carbon::parse($to)->endOfDay());
            }
        };

        // ====== Query base por rol (scoping sucursal para no-admin) ======
        $baseScope = function ($q) use ($user, $isAdmin) {
            if (! $isAdmin) {
                $q->where('branch_id', $user->branch_id);
            }
        };

        // ====== VEHÍCULOS ======
        $vehicles = \App\Models\Access::query()
            ->where('type', 'vehicle')
            ->where($baseScope)
            ->with(['user', 'branch'])
            ->withCount(['people as inside_count' => function ($q) {
                $q->whereNull('exit_at');
            }])
            ->when($qVeh, function ($q) use ($qVeh) {
                $term = trim($qVeh);
                $q->where(function ($qq) use ($term) {
                    $qq->where('full_name', 'like', "%{$term}%")
                        ->orWhere('document', 'like', "%{$term}%")
                        ->orWhere('plate', 'like', "%{$term}%");
                });
            })
            ->when($isAdmin && $branchVeh, fn($q) => $q->where('branch_id', $branchVeh))
            ->when($statusVeh === 'inside', fn($q) => $q->whereHas('people', fn($qq) => $qq->whereNull('exit_at')))
            ->when($statusVeh === 'closed', fn($q) => $q->whereNotNull('exit_at'))
            ->when($statusVeh === 'pending', fn($q) => $q->whereNull('exit_at')->whereDoesntHave('people', fn($qq) => $qq->whereNull('exit_at')))
            ->when($fromVeh || $toVeh, fn($q) => $q->where(function ($qq) use ($applyDateRange, $fromVeh, $toVeh) {
                $applyDateRange($qq, $fromVeh, $toVeh);
            }))
            ->latest('entry_at')
            ->paginate(10, ['*'], 'veh_page');

        // ====== PEATONES ======
        $pedestrians = \App\Models\Access::query()
            ->where('type', 'pedestrian')
            ->where($baseScope)
            ->with(['user', 'branch'])
            ->withCount(['people as inside_count' => function ($q) {
                $q->whereNull('exit_at');
            }])
            ->when($qPed, function ($q) use ($qPed) {
                $term = trim($qPed);
                $q->where(function ($qq) use ($term) {
                    $qq->where('full_name', 'like', "%{$term}%")
                        ->orWhere('document', 'like', "%{$term}%");
                });
            })
            ->when($isAdmin && $branchPed, fn($q) => $q->where('branch_id', $branchPed))
            ->when($statusPed === 'inside', fn($q) => $q->whereHas('people', fn($qq) => $qq->whereNull('exit_at')))
            ->when($statusPed === 'closed', fn($q) => $q->whereNotNull('exit_at'))
            ->when($statusPed === 'pending', fn($q) => $q->whereNull('exit_at')->whereDoesntHave('people', fn($qq) => $qq->whereNull('exit_at')))
            ->when($fromPed || $toPed, fn($q) => $q->where(function ($qq) use ($applyDateRange, $fromPed, $toPed) {
                $applyDateRange($qq, $fromPed, $toPed);
            }))
            ->latest('entry_at')
            ->paginate(10, ['*'], 'ped_page');

        // Pasar sucursales (para selects en la vista); evita hacer consultas en Blade
        $branches = \App\Models\Branch::orderBy('name')->get();

        return view('accesos.index', compact(
            'vehicles', 'pedestrians', 'branches',
            'qVeh', 'branchVeh', 'statusVeh', 'fromVeh', 'toVeh',
            'qPed', 'branchPed', 'statusPed', 'fromPed', 'toPed',
            'isAdmin'
        ));
    }

    // Activos: accesos con al menos 1 persona dentro
    public function active()
    {
        $user     = auth()->user();
        $isAdmin  = $user->hasRole('admin');
        $branchId = request('branch_id');

        $accesses = \App\Models\Access::query()
            ->with(['user', 'branch'])
            ->with(['people' => function ($q) {
                $q->whereNull('exit_at');
            }])
            ->whereHas('people', function ($q) {
                $q->whereNull('exit_at');
            })
            ->when(! $isAdmin, fn($q) => $q->where('branch_id', $user->branch_id))
            ->when($isAdmin && $branchId, fn($q) => $q->where('branch_id', $branchId))
            ->latest('entry_at')
            ->paginate(20);

        $branches = \App\Models\Branch::orderBy('name')->get();

        return view('accesos.activos', compact('accesses', 'branches', 'isAdmin', 'branchId'));
    }

    /*
     |------------------------------------------------------------------
     |  ENTRADAS
     |------------------------------------------------------------------
     */

    public function create()
    {
        $user    = auth()->user();
        $isAdmin = $user->hasRole('admin');

        // Sucursales solo si es admin (para seleccionar)
        $branches = $isAdmin ? \App\Models\Branch::orderBy('name')->get() : collect();

        return view('accesos.create', compact('isAdmin', 'branches'));
    }

    public function show(\App\Models\Access $access)
    {
        $access->load([
            'user',
            'branch', // 👈 para mostrar sucursal en la vista
            'people' => function ($q) {
                $q->orderByRaw('is_driver DESC')->orderBy('full_name');
            },
        ]);

        $insideCount = $access->people->whereNull('exit_at')->count();
        $driverEntry = $access->people->firstWhere('is_driver', true);
        $driverExit  = null;
        if ($access->vehicle_exit_driver_id) {
            $driverExit = $access->people->firstWhere('id', $access->vehicle_exit_driver_id);
        }

        return view('accesos.show', compact('access', 'insideCount', 'driverEntry', 'driverExit'));
    }

    public function store(Request $request)
    {
        $user    = auth()->user();
        $isAdmin = $user->hasRole('admin');

        // Normaliza placa a MAYÚSCULAS si viene
        if ($request->filled('plate')) {
            $plate = strtoupper(preg_replace('/\s+/', '', (string) $request->plate));
            $request->merge(['plate' => $plate]);
        }

        // Validaciones base SOLO con columnas/inputs que impactan tablas reales
        $rules = [
            'type'                   => ['required', Rule::in(['vehicle', 'pedestrian'])],

            // persona principal (chofer o peatón)
            'full_name'              => ['required', 'string', 'max:120'],
            'document'               => ['required', 'string', 'max:50'],

            // gender (se guarda en tabla people.gender)
            'gender'                 => ['nullable', 'string', 'max:20'],

            // notas (accesses.entry_note)
            'entry_note'             => ['nullable', 'string', 'max:2000'],

            // vehículo (opcionales) - columnas reales en accesses
            'plate'                  => ['nullable', 'regex:/^[A-Z0-9-]{3,10}$/'],
            'marca_vehiculo'         => ['nullable', 'string', 'max:60'],
            'color_vehiculo'         => ['nullable', 'string', 'max:40'],
            'tipo_vehiculo'          => ['nullable', Rule::in(['auto', 'moto', 'bicicleta', 'camion'])],

            // acompañantes (access_people & people)
            'passengers'             => ['nullable', 'array'],
            'passengers.*.full_name' => ['nullable', 'string', 'max:120'],
            'passengers.*.document'  => ['nullable', 'string', 'max:50'],
            'passengers.*.gender'    => ['nullable', 'string', 'max:20'],
        ];

        // 👇 Exigir sucursal a administradores
        if ($isAdmin) {
            $rules['branch_id'] = ['required', 'exists:branches,id'];
        }

        if ($request->type === 'vehicle') {
            $rules['plate'] = array_merge(['required'], $rules['plate']);
        }

        $data = $request->validate($rules, [
            'plate.required' => 'La placa es obligatoria para vehículos.',
            'plate.regex'    => 'Formato de placa inválido. Ejemplo: ABC-123.',
        ]);

        /*
    |--------------------------------------------------------------
    |  A) Evitar documentos duplicados dentro del mismo formulario
    |--------------------------------------------------------------
    */
        $docs    = [];
        $pushDoc = function ($doc, $label) use (&$docs) {
            $doc = trim((string) $doc);
            if ($doc === '') {
                return;
            }

            if (in_array($doc, $docs, true)) {
                throw ValidationException::withMessages([
                    'document' => "El documento '{$doc}' está repetido dentro del acceso ({$label}).",
                ]);
            }
            $docs[] = $doc;
        };
        $pushDoc($data['document'], 'principal');
        foreach ($request->input('passengers', []) as $p) {
            if (! empty($p['document'])) {
                $pushDoc($p['document'], 'acompañantes');
            }

        }

        /*
    |--------------------------------------------------------------
    |  B) Verificar que NADIE esté ya dentro por documento
    |--------------------------------------------------------------
    */
        $docsToCheck = collect([$data['document']])
            ->merge(collect($request->input('passengers', []))->pluck('document')->filter()->values());

        if ($docsToCheck->isNotEmpty()) {
            $alreadyInsideDocs = AccessPerson::whereNull('exit_at')
                ->whereIn('document', $docsToCheck)
                ->pluck('document')->unique()->all();

            if (! empty($alreadyInsideDocs)) {
                $list = implode(', ', $alreadyInsideDocs);
                throw ValidationException::withMessages([
                    'document' => "Los siguientes documentos (personas) ya se encuentran dentro: {$list}.",
                ]);
            }
        }

        /*
    |--------------------------------------------------------------
    |  C) Verificar que el vehículo (placa) no esté ya dentro
    |--------------------------------------------------------------
    */
        if ($data['type'] === 'vehicle') {
            $plateActive = Access::where('type', 'vehicle')
                ->where('plate', $data['plate'])
                ->whereNull('vehicle_exit_at')
                ->exists();

            if ($plateActive) {
                throw ValidationException::withMessages([
                    'plate' => 'Ese vehículo (placa) ya se encuentra dentro. Registre la salida antes de una nueva entrada.',
                ]);
            }
        }

        /*
    |--------------------------------------------------------------
    |  D) Crear Access + personas (y upsert al maestro people)
    |--------------------------------------------------------------
    */

        // 👇 Para no-admin, forzar sucursal del usuario
        if (! $isAdmin) {
            $request->merge(['branch_id' => $user->branch_id]);
        }

        DB::transaction(function () use ($request, $data) {
            $access = Access::create([
                'branch_id'      => $request->branch_id,
                'type'           => $data['type'],
                'plate'          => $data['type'] === 'vehicle' ? $data['plate'] : null,
                'marca_vehiculo' => $data['type'] === 'vehicle' ? $request->input('marca_vehiculo') : null,
                'color_vehiculo' => $data['type'] === 'vehicle' ? $request->input('color_vehiculo') : null,
                'tipo_vehiculo'  => $data['type'] === 'vehicle' ? $request->input('tipo_vehiculo') : null,
                'entry_at'       => now('America/Asuncion'),
                'entry_note'     => $request->input('entry_note'),
                'user_id'        => auth()->id(),
                'full_name'      => $data['full_name'],
                'document'       => $data['document'],
            ]);

            // Upsert persona principal (maestro)
            Person::updateOrCreate(
                ['document' => $data['document']],
                ['full_name' => $data['full_name'], 'gender' => $request->input('gender')]
            );

            if ($data['type'] === 'vehicle') {
                // Chofer
                $access->people()->create([
                    'full_name' => $data['full_name'],
                    'document'  => $data['document'],
                    'role'      => 'driver',
                    'is_driver' => true,
                    'gender'    => $request->input('gender'),
                    'entry_at'  => now('America/Asuncion'),
                ]);

                // Acompañantes opcionales
                foreach ($request->input('passengers', []) as $p) {
                    $p = array_map('trim', (array) $p);
                    if (! empty($p['full_name']) && ! empty($p['document'])) {
                        $pGender = $p['gender'] ?? null;

                        Person::updateOrCreate(
                            ['document' => $p['document']],
                            ['full_name' => $p['full_name'], 'gender' => $pGender]
                        );

                        $access->people()->create([
                            'full_name' => $p['full_name'],
                            'document'  => $p['document'],
                            'role'      => 'passenger',
                            'is_driver' => false,
                            'gender'    => $pGender,
                            'entry_at'  => now('America/Asuncion'),
                        ]);
                    }
                }
            } else {
                // Peatón
                $access->people()->create([
                    'full_name' => $data['full_name'],
                    'document'  => $data['document'],
                    'role'      => 'pedestrian',
                    'is_driver' => false,
                    'gender'    => $request->input('gender'),
                    'entry_at'  => now('America/Asuncion'),
                ]);
            }
        });

        Log::info('access.created', [
            'access_id' => $access->id ?? null,
            'type'      => $data['type'],
            'plate'     => $data['type'] === 'vehicle' ? ($data['plate'] ?? null) : null,
            'user_id'   => auth()->id(),
            'entry_at'  => now('America/Asuncion')->toDateTimeString(),
            'people'    => [
                'main_document' => $data['document'],
                'passengers'    => collect($request->input('passengers', []))
                    ->filter(fn($p) => ! empty($p['document']))
                    ->pluck('document')->values()->all(),
            ],
        ]);

        return redirect()->route('access.index')->with('success', 'Entrada registrada correctamente.');
    }

    /*
     |------------------------------------------------------------------
     |  SALIDAS
     |------------------------------------------------------------------
     */

    public function exitForm()
    {
        // Listado de accesos con gente dentro (paginado y con sucursal)
        $activeAccesses = \App\Models\Access::query()
            ->with([
                'branch',
                'people' => fn($q) => $q->whereNull('exit_at'),
            ])
            ->whereNull('exit_at')
            ->latest('entry_at')
            ->paginate(15);

        return view('accesos.exit', compact('activeAccesses'));
    }

    // Buscar por placa o documento (activos)
    public function search(Request $request)
    {
        $request->validate([
            'plate'    => ['nullable', 'string'],
            'document' => ['nullable', 'string'],
        ]);

        $access = null;
        $person = null;

        // Búsqueda por placa (vehículo activo)
        if ($request->filled('plate')) {
            $plate  = strtoupper(trim($request->plate));
            $access = \App\Models\Access::where('type', 'vehicle')
                ->where('plate', $plate)
                ->whereHas('people', fn($q) => $q->whereNull('exit_at'))
                ->with([
                    'branch',
                    'people' => fn($q) => $q->whereNull('exit_at'),
                ])
                ->first();
        }

        // Búsqueda por documento (persona activa)
        if (! $access && $request->filled('document')) {
            $doc    = trim($request->document);
            $person = \App\Models\AccessPerson::whereNull('exit_at')
                ->where('document', $doc)
                ->with('access')
                ->first();

            if ($person) {
                $access = $person->access()
                    ->with([
                        'branch',
                        'people' => fn($q) => $q->whereNull('exit_at'),
                    ])
                    ->first();
            }
        }

        // También traemos el listado de activos para mostrar si no hay resultado
        $activeAccesses = null;
        if (! $access) {
            $activeAccesses = \App\Models\Access::query()
                ->with([
                    'branch',
                    'people' => fn($q) => $q->whereNull('exit_at'),
                ])
                ->whereNull('exit_at')
                ->latest('entry_at')
                ->paginate(15);
        }

        return view('accesos.exit', compact('access', 'person', 'activeAccesses'))
            ->with('success', $access ? null : 'No se encontraron resultados activos.');
    }

    // Registrar salida (vehículo/personas)
    public function registerExit(Request $request, Access $access)
    {
        $data = $request->validate([
            'vehicle_exit'           => ['nullable', 'boolean'],
            'vehicle_exit_driver_id' => [
                'nullable',
                'integer',
                Rule::exists('access_people', 'id')->where(fn($q) => $q->where('access_id', $access->id)),
            ],
            'people_exit'            => ['nullable', 'array'],
            'people_exit.*'          => ['integer', 'exists:access_people,id'],
            'exit_note'              => ['nullable', 'string', 'max:2000'],
        ]);

        $now = now('America/Asuncion');
        $ids = collect($request->input('people_exit', []))->unique()->values();

        DB::transaction(function () use ($request, $access, $ids, $now) {
            // Si sale vehículo, registrar salida y conductor
            if ($request->boolean('vehicle_exit') && $access->type === 'vehicle' && is_null($access->vehicle_exit_at)) {
                $driverOutId                    = (int) $request->input('vehicle_exit_driver_id');
                $access->vehicle_exit_at        = $now;
                $access->vehicle_exit_driver_id = $driverOutId;
                $access->save();
                Log::info('access.vehicle_exit', [
                    'access_id' => $access->id,
                    'driver_id' => (int) $request->input('vehicle_exit_driver_id'),
                    'at'        => $now->toDateTimeString(),
                    'user_id'   => auth()->id(),
                ]);
            }

            // Salida de personas seleccionadas
            if ($ids->isNotEmpty()) {
                AccessPerson::where('access_id', $access->id)
                    ->whereIn('id', $ids)
                    ->whereNull('exit_at')
                    ->update(['exit_at' => $now]);
                Log::info('access.people_exit', [
                    'access_id'  => $access->id,
                    'people_ids' => $ids->all(),
                    'at'         => $now->toDateTimeString(),
                    'user_id'    => auth()->id(),
                ]);
            }

            // Cerrar access si ya no queda nadie dentro; guardar nota
            $stillInside = AccessPerson::where('access_id', $access->id)
                ->whereNull('exit_at')
                ->count();

            Log::info('access.closed_check', [
                'access_id'   => $access->id,
                'stillInside' => $stillInside,
                'exit_at'     => optional($access->exit_at)->toDateTimeString(),
            ]);

            if ($stillInside === 0) {
                $access->exit_at = $now;
                if ($request->filled('exit_note')) {
                    $access->exit_note = $request->input('exit_note');
                }
                $access->save();
            } elseif ($request->filled('exit_note')) {
                $access->exit_note = $request->input('exit_note');
                $access->save();
            }
        });

        return back()->with('success', 'Salida registrada correctamente.');
    }
}
