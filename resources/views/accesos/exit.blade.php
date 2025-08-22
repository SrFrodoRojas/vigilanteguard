@extends('adminlte::page')
@section('title', 'Registrar salida')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap">
        <div>
            <h1 class="mb-0">Registrar salida</h1>
            <small class="text-muted">Busca por placa o documento y registra la salida parcial o total</small>
        </div>
        <div class="mt-2 mt-md-0 d-flex flex-wrap">
            <a href="{{ route('access.index') }}" class="btn btn-secondary btn-sm mr-2 mb-2">
                <i class="fas fa-list"></i> Listados
            </a>
            <a href="{{ route('access.active') }}" class="btn btn-outline-warning btn-sm mr-2 mb-2">
                <i class="fas fa-user-check"></i> Ver Activos
            </a>
            <a href="{{ route('access.create') }}" class="btn btn-outline-primary btn-sm mb-2">
                <i class="fas fa-sign-in-alt"></i> Registrar entrada
            </a>
        </div>
    </div>
@endsection

@section('content')
    @if (session('success'))
        <x-adminlte-alert theme="info">{{ session('success') }}</x-adminlte-alert>
    @endif
    @if ($errors->any())
        <x-adminlte-alert theme="danger" title="Revisa los datos">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    {{-- BÚSQUEDA --}}
    <x-adminlte-card theme="secondary" icon="fas fa-search" title="Buscar registro activo" class="mb-3">
        <form method="GET" action="{{ route('access.search') }}" autocomplete="off">
            <div class="form-row">
                <div class="form-group col-12 col-md-4">
                    <label>Placa (vehículo)</label>
                    <input name="plate" class="form-control form-control-sm" value="{{ request('plate') }}"
                           placeholder="ABC-123" style="text-transform:uppercase">
                    <small class="text-muted">Busca un vehículo activo por placa.</small>
                </div>
                <div class="form-group col-12 col-md-4">
                    <label>Documento</label>
                    <input name="document" class="form-control form-control-sm" value="{{ request('document') }}"
                           placeholder="1234567">
                    <small class="text-muted">O busca cualquier persona activa por documento.</small>
                </div>
                <div class="form-group col-12 col-md-4 d-flex align-items-end">
                    <button class="btn btn-primary btn-sm btn-block">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </div>
        </form>
    </x-adminlte-card>

    {{-- RESULTADO DE LA BÚSQUEDA (si hay) --}}
    @if (!empty($access))
        <x-adminlte-card theme="light" icon="fas fa-info-circle" title="Resultado de la búsqueda" class="mb-3">
            <div class="row">
                <div class="col-12 col-md-6">
                    <p class="mb-1"><strong>#ID:</strong> {{ $access->id }}</p>
                    <p class="mb-1"><strong>Tipo:</strong> {{ $access->type === 'vehicle' ? 'Vehículo' : 'A pie' }}</p>
                    @if($access->branch)
                        <p class="mb-1"><strong>Sucursal:</strong> {{ $access->branch->name }}</p>
                    @endif
                    @if ($access->type === 'vehicle')
                        <p class="mb-1"><strong>Placa:</strong> {{ $access->plate }}</p>
                    @endif
                </div>
                <div class="col-12 col-md-6">
                    <p class="mb-1"><strong>Entrada:</strong>
                        {{ $access->entry_at?->timezone('America/Asuncion')->format('d/m/Y H:i') }}</p>
                    <p class="mb-1"><strong>Ocupantes dentro:</strong>
                        {{ $access->people->count() }}</p>
                </div>
            </div>
        </x-adminlte-card>

        {{-- FORMULARIO DE SALIDA --}}
        @php
            $inside      = $access->people; // ya viene filtrado con exit_at NULL
            $insideCount = $inside->count();
        @endphp

        <x-adminlte-card theme="warning" icon="fas fa-sign-out-alt" title="Registrar salida">
            <form method="POST" action="{{ route('access.registerExit', $access) }}" id="exit-form">
                @csrf

                {{-- Vehículo sale --}}
                @if ($access->type === 'vehicle')
                    <div class="custom-control custom-switch mb-3">
                        <input type="checkbox" class="custom-control-input" id="vehicle_exit" name="vehicle_exit"
                               value="1" {{ $access->vehicle_exit_at ? 'checked disabled' : '' }}>
                        <label class="custom-control-label" for="vehicle_exit">
                            El vehículo sale ahora
                            @if ($access->vehicle_exit_at)
                                <small class="text-muted">(ya registrado)</small>
                            @endif
                        </label>
                    </div>
                @endif

                {{-- Conductor al salir (si el vehículo sale ahora) --}}
                @if ($access->type === 'vehicle' && !$access->vehicle_exit_at)
                    <div id="driver-out-wrap" class="form-group mt-2" style="display:none;">
                        <label>¿Quién conduce al salir?</label>
                        <select name="vehicle_exit_driver_id" id="vehicle_exit_driver_id" class="form-control form-control-sm">
                            <option value="">— Seleccionar —</option>
                            @foreach ($inside as $p)
                                <option value="{{ $p->id }}">
                                    {{ $p->full_name }} ({{ $p->document }})
                                    @if ($p->is_driver) — Chofer de entrada @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Debe coincidir con una de las personas marcadas para salir.</small>
                    </div>
                @endif

                {{-- Personas dentro --}}
                @if ($access->type === 'pedestrian' && $insideCount === 1)
                    <div class="alert alert-info">
                        Se registrará la salida de <strong>{{ $inside->first()->full_name }}</strong>.
                    </div>
                @else
                    <div class="d-flex align-items-center mb-2">
                        <h6 class="mb-0 mr-3">Ocupantes dentro</h6>
                        <button type="button" class="btn btn-xs btn-outline-secondary mr-2" id="mark-all">Marcar todos</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" id="unmark-all">Desmarcar</button>
                    </div>
                    @foreach ($inside as $p)
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input person-exit" id="person_{{ $p->id }}"
                                   name="people_exit[]" value="{{ $p->id }}">
                            <label class="custom-control-label" for="person_{{ $p->id }}">
                                {{ $p->full_name }} <small class="text-muted">({{ $p->document }})</small>
                                @if ($p->is_driver)
                                    <span class="badge badge-info">Chofer</span>
                                @endif
                            </label>
                        </div>
                    @endforeach
                @endif

                {{-- Observación de salida --}}
                <div class="form-group mt-3">
                    <label>Observación de salida (opcional)</label>
                    <textarea name="exit_note" class="form-control form-control-sm" rows="2"
                              placeholder="Ej.: salida parcial, vehículo fuera y quedan 2 personas, etc."></textarea>
                </div>

                <div class="mt-3 d-flex flex-wrap gap-2">
                    <button class="btn btn-success btn-sm">
                        <i class="fas fa-check"></i> Registrar salida
                    </button>
                    <a href="{{ route('access.exit.form') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Nueva búsqueda
                    </a>
                </div>
            </form>
        </x-adminlte-card>
    @endif

    {{-- LISTADO DE ACTIVOS (solo si no hay resultado puntual) --}}
    @if (empty($access) && !empty($activeAccesses))
        <x-adminlte-card theme="info" icon="fas fa-users" title="Registros activos" class="mb-3">
            {{-- Mobile: cards --}}
            <div class="d-md-none">
                @forelse($activeAccesses as $a)
                    @php
                        $isVehicle   = $a->type === 'vehicle';
                        $insidePeople= $a->people; // ya viene filtrado
                        $insideCount = $insidePeople->count();
                        $vehicleOut  = $isVehicle && !empty($a->vehicle_exit_at);
                        $firstDoc    = optional($insidePeople->first())->document;
                    @endphp
                    <div class="card mb-2">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between">
                                <span class="badge {{ $isVehicle ? 'badge-primary' : 'badge-success' }}">
                                    {{ $isVehicle ? 'Vehículo' : 'A pie' }}
                                </span>
                                <small>#{{ $a->id }}</small>
                            </div>

                            @if($a->branch)
                                <div class="mt-1"><i class="fas fa-warehouse"></i> {{ $a->branch->name }}</div>
                            @endif
                            @if($a->plate)
                                <div class="mt-1 mb-1"><i class="fas fa-car-alt"></i> Placa: <strong>{{ $a->plate }}</strong></div>
                            @endif

                            <div class="text-muted mb-2">
                                <i class="far fa-clock"></i>
                                Entrada: {{ $a->entry_at?->timezone('America/Asuncion')->format('d/m H:i') }}
                            </div>

                            <div class="mb-2">
                                <span class="badge badge-warning">Dentro: {{ $insideCount }}</span>
                                @if($vehicleOut)
                                    <span class="badge badge-info">Vehículo fuera</span>
                                @endif
                            </div>

                            @if($insidePeople->isNotEmpty())
                                <div class="mb-2">
                                    @foreach($insidePeople as $p)
                                        <span class="badge badge-secondary mr-1 mb-1">
                                            {{ $p->full_name }}@if($p->is_driver) <small class="text-light"> (Chofer)</small>@endif
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <form method="GET" action="{{ route('access.search') }}">
                                @if($isVehicle && $a->plate)
                                    <input type="hidden" name="plate" value="{{ $a->plate }}">
                                @elseif($firstDoc)
                                    <input type="hidden" name="document" value="{{ $firstDoc }}">
                                @endif
                                <button class="btn btn-success btn-block">
                                    <i class="fas fa-external-link-alt"></i> Gestionar salida
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <x-adminlte-alert theme="info">No hay activos.</x-adminlte-alert>
                @endforelse

                <div class="mt-2">
                    {{ $activeAccesses->onEachSide(1)->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
            </div>

            {{-- Desktop: tabla --}}
            <div class="table-responsive d-none d-md-block">
                <table class="table table-striped table-sm align-middle">
                    <thead class="thead-light sticky-top">
                        <tr>
                            <th>#</th>
                            <th>Tipo</th>
                            <th>Sucursal</th>
                            <th>Placa</th>
                            <th>Ocupantes dentro</th>
                            <th>Entrada</th>
                            <th style="width:1%;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeAccesses as $a)
                            @php
                                $isVehicle   = $a->type === 'vehicle';
                                $insidePeople= $a->people;
                                $insideCount = $insidePeople->count();
                                $firstDoc    = optional($insidePeople->first())->document;
                            @endphp
                            <tr>
                                <td>{{ $a->id }}</td>
                                <td>{{ $isVehicle ? 'Vehículo' : 'A pie' }}</td>
                                <td>{{ $a->branch->name ?? '—' }}</td>
                                <td>{{ $a->plate ?? '—' }}</td>
                                <td>
                                    @forelse($insidePeople as $p)
                                        <span class="badge badge-secondary mr-1 mb-1">
                                            {{ $p->full_name }}@if($p->is_driver)<small class="text-light"> (Chofer)</small>@endif
                                        </span>
                                    @empty
                                        <span class="text-muted">—</span>
                                    @endforelse
                                </td>
                                <td>{{ $a->entry_at?->timezone('America/Asuncion')->format('d/m/Y H:i') }}</td>
                                <td class="text-nowrap">
                                    <form method="GET" action="{{ route('access.search') }}">
                                        @if($isVehicle && $a->plate)
                                            <input type="hidden" name="plate" value="{{ $a->plate }}">
                                        @elseif($firstDoc)
                                            <input type="hidden" name="document" value="{{ $firstDoc }}">
                                        @endif
                                        <button class="btn btn-sm btn-success" title="Gestionar salida">
                                            <i class="fas fa-external-link-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7">No hay personas/vehículos dentro.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $activeAccesses->onEachSide(1)->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        </x-adminlte-card>
    @endif
@endsection

@push('css')
<style>
    .thead-light.sticky-top { top: 0; z-index: 1; }

    /* Compacto en móvil */
    @media (max-width: 767.98px) {
        .btn { padding: .35rem .6rem; font-size: .9rem; }
        .form-control, .form-control-sm, .input-group-text { font-size: .9rem; }
        .card-body { padding: .9rem; }
        .table { font-size: .9rem; }
        label { margin-bottom: .2rem; }
    }

    /* Paginación compacta */
    .pagination { margin-bottom: 0; }
    .pagination .page-link { padding: .25rem .5rem; font-size: .875rem; }
    .pagination .page-item { margin: 0 2px; }
</style>
@endpush

@push('js')
<script>
(function () {
    // Mayúsculas en placa
    const plateInput = document.querySelector('input[name="plate"]');
    if (plateInput) plateInput.addEventListener('input', () => plateInput.value = plateInput.value.toUpperCase());

    // UI conductor al salir
    const vehicleExit = document.getElementById('vehicle_exit');
    const driverWrap  = document.getElementById('driver-out-wrap');
    const driverSel   = document.getElementById('vehicle_exit_driver_id');
    const personCbs   = Array.from(document.querySelectorAll('.person-exit'));

    function syncDriverUI() {
        if (vehicleExit && driverWrap) {
            driverWrap.style.display = (vehicleExit.checked && !vehicleExit.disabled) ? '' : 'none';
            if (driverSel && driverWrap.style.display === 'none') driverSel.value = '';
        }
    }

    if (vehicleExit) {
        vehicleExit.addEventListener('change', syncDriverUI);
        syncDriverUI();
    }

    // Si elijo conductor, marcar su checkbox de salida
    if (driverSel) {
        driverSel.addEventListener('change', () => {
            const id = driverSel.value;
            if (!id) return;
            const cb = personCbs.find(x => x.value === id);
            if (cb && !cb.checked && !cb.disabled) cb.checked = true;
        });
    }

    // Marcar/Desmarcar todos
    const markAllBtn   = document.getElementById('mark-all');
    const unmarkAllBtn = document.getElementById('unmark-all');
    if (markAllBtn)   markAllBtn.addEventListener('click', () => personCbs.forEach(cb => { if (!cb.disabled) cb.checked = true; }));
    if (unmarkAllBtn) unmarkAllBtn.addEventListener('click', () => personCbs.forEach(cb => { if (!cb.disabled) cb.checked = false; }));
})();
</script>
@endpush
