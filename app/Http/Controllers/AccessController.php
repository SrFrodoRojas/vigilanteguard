<?php
namespace App\Http\Controllers;

use App\Models\Access;
use App\Models\AccessPerson;
use App\Models\Branch;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AccessController extends Controller
{
    /* ==========================
     * LISTADOS / VISTAS
     * ========================== */

    /** GET: /accesos (listado combinado de vehículos y peatones, con filtros) */
    public function index(Request $request)
    {
        $user    = $request->user();
        $isAdmin = $user && method_exists($user, 'hasRole') ? $user->hasRole('admin') : false;

        // Filtros vehículos
        $qVeh      = $request->input('q_veh');
        $branchVeh = $request->input('branch_veh');
        $statusVeh = $request->input('status_veh'); // inside|closed|pending|null
        $fromVeh   = $request->input('from_veh');
        $toVeh     = $request->input('to_veh');

        // Filtros peatones
        $qPed      = $request->input('q_ped');
        $branchPed = $request->input('branch_ped');
        $statusPed = $request->input('status_ped'); // inside|closed|pending|null
        $fromPed   = $request->input('from_ped');
        $toPed     = $request->input('to_ped');

        $applyDateRange = function ($q, $from, $to) {
            if ($from) {
                $q->where('entry_at', '>=', Carbon::parse($from)->startOfDay());
            }
            if ($to) {
                $q->where('entry_at', '<=', Carbon::parse($to)->endOfDay());
            }
        };

        $baseScope = function ($q) use ($user, $isAdmin) {
            if (! $isAdmin && $user) {
                $q->where('branch_id', $user->branch_id);
            }
        };

        // Vehículos
        $vehicles = Access::query()
            ->where('type', 'vehicle')
            ->where($baseScope)
            ->with(['user', 'branch'])
            ->withCount(['people as inside_count' => fn($q) => $q->whereNull('exit_at')])
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
            ->when($fromVeh || $toVeh, fn($q) => $q->where(fn($qq) => $applyDateRange($qq, $fromVeh, $toVeh)))
            ->latest('entry_at')
            ->paginate(10, ['*'], 'veh_page');

        // Peatones
        $pedestrians = Access::query()
            ->where('type', 'pedestrian')
            ->where($baseScope)
            ->with(['user', 'branch'])
            ->withCount(['people as inside_count' => fn($q) => $q->whereNull('exit_at')])
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
            ->when($fromPed || $toPed, fn($q) => $q->where(fn($qq) => $applyDateRange($qq, $fromPed, $toPed)))
            ->latest('entry_at')
            ->paginate(10, ['*'], 'ped_page');

        $branches = Branch::orderBy('name')->get();

        return view('accesos.index', compact(
            'vehicles', 'pedestrians', 'branches',
            'qVeh', 'branchVeh', 'statusVeh', 'fromVeh', 'toVeh',
            'qPed', 'branchPed', 'statusPed', 'fromPed', 'toPed',
            'isAdmin'
        ));
    }

    // Alias para que coincida con tu ruta: access.active -> AccessController@active
    public function active(Request $request)
    {
        // reutiliza la lógica existente
        return $this->activos($request);
    }

// Alias para que coincida con tu ruta: access.exit.form -> AccessController@exitForm
    public function exitForm(Request $request)
    {
        // reutiliza la lógica existente
        return $this->exitIndex($request);
    }

    /** GET: /accesos/activos (solo accesos abiertos) */
    public function activos(Request $request)
    {
        $user     = $request->user();
        $isAdmin  = $user && method_exists($user, 'hasRole') ? $user->hasRole('admin') : false;
        $branchId = $request->input('branch_id');

        $accesses = Access::query()
            ->with(['user', 'branch'])
            ->with(['people' => fn($q) => $q->whereNull('exit_at')])
            ->whereHas('people', fn($q) => $q->whereNull('exit_at'))
            ->when(! $isAdmin && $user, fn($q) => $q->where('branch_id', $user->branch_id))
            ->when($isAdmin && $branchId, fn($q) => $q->where('branch_id', $branchId))
            ->latest('entry_at')
            ->paginate(20);

        $branches = Branch::orderBy('name')->get();

        return view('accesos.activos', compact('accesses', 'branches', 'isAdmin', 'branchId'));
    }

    /** GET: /accesos/create (form crear) */
    public function create(Request $request)
    {
        $user    = $request->user();
        $isAdmin = $user && method_exists($user, 'hasRole') ? $user->hasRole('admin') : false;

        $branches = $isAdmin ? Branch::orderBy('name')->get() : collect();

        return view('accesos.create', compact('isAdmin', 'branches'));
    }

    /** GET: /accesos/{access} (detalle) */
    public function show(Access $access)
    {
        $access->load([
            'user',
            'branch',
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

    /* ==========================
     * ESCRITURA (crear/editar/salidas)
     * ========================== */

    /** POST: /accesses (crear acceso) */
public function store(\Illuminate\Http\Request $request)
{
    // 1) Validación (aceptamos también campos top-level de tu form)
    $data = $request->validate([
        'type'           => ['required','in:vehicle,pedestrian'],
        'plate'          => ['required_if:type,vehicle','nullable','string','max:20'],
        'marca_vehiculo' => ['nullable','string','max:50'],
        'color_vehiculo' => ['nullable','string','max:30'],
        'tipo_vehiculo'  => ['nullable','string','max:30'],
        'entry_note'     => ['nullable','string','max:255'],

        // top-level (tu form los manda así)
        'full_name'      => ['nullable','string','max:120'],
        'document'       => ['nullable','string','max:50'],
        'gender'         => ['nullable','string','max:20'],

        // ocupantes (opcional)
        'people'                 => ['array'],
        'people.*.full_name'     => ['required','string','max:120'],
        'people.*.document'      => ['required','string','max:50'],
        'people.*.gender'        => ['nullable','string','max:20'],
        'people.*.role'          => ['required','in:driver,passenger,pedestrian'],
        'people.*.is_driver'     => ['boolean'],
        // branch (solo si el admin selecciona)
        'branch_id'      => ['nullable','integer'],
    ]);

    // 2) Armar la lista de ocupantes a partir de:
    //    - people[] si vino
    //    - o de los campos top-level (full_name/document) si vinieron sin people[]
    $people = $data['people'] ?? [];
    if (empty($people) && !empty($data['full_name']) && !empty($data['document'])) {
        $people[] = [
            'full_name' => $data['full_name'],
            'document'  => $data['document'],
            'gender'    => $data['gender'] ?? null,
            'role'      => $data['type'] === 'vehicle' ? 'driver' : 'pedestrian',
            'is_driver' => $data['type'] === 'vehicle',
        ];
    }

    // 3) Anti-duplicados (placa/doc activos)
    $docs = collect($people)->pluck('document')->filter()->all();
    $this->guardAgainstDuplicates($data['type'], $data['plate'] ?? null, $docs);

    // 4) Determinar branch (admin puede elegir; otros, su propia sucursal)
    $user    = $request->user();
    $isAdmin = $user && method_exists($user, 'hasRole') ? $user->hasRole('admin') : false;
    $branchId = $user?->branch_id;
    if ($isAdmin && $request->filled('branch_id')) {
        $branchId = (int) $request->input('branch_id');
    }

    // 5) Tomar el "primario" para cumplir NOT NULL de accesses.full_name/document
    $primary = !empty($people) ? $people[0] : ['full_name' => 'N/D', 'document' => 'N/D'];

    // 6) Escritura atómica: insert con full_name/document/people_count y luego ocupantes
    $access = null;
    \Illuminate\Support\Facades\DB::transaction(function () use (&$access, $data, $people, $primary, $branchId) {
        $access = \App\Models\Access::create([
            'type'           => $data['type'],
            'plate'          => $data['plate'] ?? null,
            'marca_vehiculo' => $data['marca_vehiculo'] ?? null,
            'color_vehiculo' => $data['color_vehiculo'] ?? null,
            'tipo_vehiculo'  => $data['tipo_vehiculo'] ?? null,
            'entry_at'       => now(),
            'entry_note'     => $data['entry_note'] ?? null,
            'user_id'        => auth()->id(),
            'branch_id'      => $branchId,

            // >>> campos NOT NULL en tu tabla
            'full_name'      => $primary['full_name'] ?? 'N/D',
            'document'       => $primary['document'] ?? 'N/D',
            'people_count'   => (string) count($people),
        ]);

        // Cargar ocupantes (si hay)
        if (!empty($people)) {
            $this->upsertPeople($access, $people);
        }

        // Ajustar denormalizados por si cambiaron (count, etc.)
        $this->denormalizeAccess($access);
    });

    // 7) Respuesta
    return redirect()
        ->route('access.show', $access)   // tu nombre de ruta
        ->with('ok', 'Acceso registrado.');
}


    /** POST: /accesses/{access}/people (agregar ocupante) */
    public function storePerson(Request $request, Access $access)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'document'  => ['required', 'string', 'max:50'],
            'gender'    => ['nullable', 'in:M,F,O'],
            'role'      => ['required', 'in:driver,passenger,pedestrian'],
            'is_driver' => ['boolean'],
        ]);

        $this->guardAgainstDuplicates('pedestrian', null, [$data['document']]);

        DB::transaction(function () use ($access, $data) {
            $access->people()->create([
                'full_name' => $data['full_name'],
                'document'  => $data['document'],
                'gender'    => $data['gender'] ?? null,
                'role'      => $data['role'],
                'is_driver' => (bool) ($data['is_driver'] ?? false),
                'entry_at'  => now(),
            ]);

            Person::updateOrCreate(
                ['document' => $data['document']],
                ['full_name' => $data['full_name'], 'gender' => $data['gender'] ?? null]
            );

            $this->denormalizeAccess($access);
        });

        return back()->with('ok', 'Ocupante agregado.');
    }

    /** GET: /accesos/exit (vista para cerrar accesos) */
    public function exitIndex(Request $request)
    {
        $activeAccesses = Access::query()
            ->with([
                'branch',
                'people' => fn($q) => $q->whereNull('exit_at'),
            ])
            ->whereNull('exit_at')
            ->latest('entry_at')
            ->paginate(15);

        return view('accesos.exit', compact('activeAccesses'));
    }

    /** POST: /accesos/exit/search (buscar por placa o documento para cerrar) */
    public function search(Request $request)
    {
        $request->validate([
            'plate'    => ['nullable', 'string'],
            'document' => ['nullable', 'string'],
        ]);

        $access = null;
        $person = null;

        if ($request->filled('plate')) {
            $plate  = strtoupper(trim($request->plate));
            $access = Access::where('type', 'vehicle')
                ->where('plate', $plate)
                ->whereHas('people', fn($q) => $q->whereNull('exit_at'))
                ->with([
                    'branch',
                    'people' => fn($q) => $q->whereNull('exit_at'),
                ])
                ->first();
        }

        if (! $access && $request->filled('document')) {
            $doc    = trim($request->document);
            $person = AccessPerson::whereNull('exit_at')
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

        $activeAccesses = null;
        if (! $access) {
            $activeAccesses = Access::query()
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

    /** POST: /accesses/{access}/exit (cerrar acceso completo) */
    public function registerExit(Request $request, Access $access)
    {
        $request->validate([
            'driver_person_id' => ['nullable', 'integer', 'exists:access_people,id'],
            'exit_note'        => ['nullable', 'string', 'max:255'],
        ]);

        abort_if($access->exit_at, 422, 'Este acceso ya está cerrado.');

        DB::transaction(function () use ($request, $access) {
            $driverId = $request->input('driver_person_id');

            if ($driverId) {
                $access->vehicle_exit_driver_id = $driverId;
                $access->vehicle_exit_at        = now();
            }

            $access->exit_at   = now();
            $access->exit_note = $request->input('exit_note');
            $access->save();

            $access->people()->whereNull('exit_at')->update(['exit_at' => now()]);
        });

        return back()->with('ok', 'Salida registrada.');
    }

    /** POST: /access-people/{person}/exit (cerrar salida individual) */
    public function registerExitPerson(AccessPerson $person)
    {
        abort_if($person->exit_at, 422, 'Ya tiene salida registrada.');
        $person->exit_at = now();
        $person->save();

        return back()->with('ok', 'Salida del ocupante registrada.');
    }

    /* ==========================
     * HELPERS PRIVADOS
     * ========================== */

    /** Verifica duplicados activos (misma placa o documentos “dentro”) con bloqueo. */
    private function guardAgainstDuplicates(string $type, ?string $plate, array $docs = []): void
    {
        DB::transaction(function () use ($type, $plate, $docs) {
            if ($type === 'vehicle' && ! empty($plate)) {
                $exists = DB::table('accesses')
                    ->where('plate', $plate)
                    ->whereNull('exit_at')
                    ->lockForUpdate()
                    ->exists();
                abort_if($exists, 422, 'Esa placa ya está “dentro”.');
            }

            $docs = array_values(array_filter(array_unique($docs)));
            if (! empty($docs)) {
                $docExists = DB::table('access_people')
                    ->whereNull('exit_at')
                    ->whereIn('document', $docs)
                    ->lockForUpdate()
                    ->exists();
                abort_if($docExists, 422, 'Alguna persona ya está “dentro”.');
            }
        }, 1); // transacción corta para lock
    }

    /** Crea/actualiza maestro y agrega ocupantes al access. */
    private function upsertPeople(Access $access, array $people = []): void
    {
        foreach ($people as $p) {
            $access->people()->create([
                'full_name' => $p['full_name'],
                'document'  => $p['document'],
                'gender'    => $p['gender'] ?? null,
                'role'      => $p['role'],
                'is_driver' => (bool) ($p['is_driver'] ?? false),
                'entry_at'  => now(),
            ]);

            Person::updateOrCreate(
                ['document' => $p['document']],
                ['full_name' => $p['full_name'], 'gender' => $p['gender'] ?? null]
            );
        }
    }

    /** Actualiza campos denormalizados en Access (people_count, full_name, document). */
    private function denormalizeAccess(Access $access): void
    {
        $access->people_count = $access->people()->count();
        if ($access->people_count > 0) {
            $first             = $access->people()->oldest('id')->first();
            $access->full_name = $first->full_name;
            $access->document  = $first->document;
        }
        $access->save();
    }
}
