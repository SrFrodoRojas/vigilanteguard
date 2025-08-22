@extends('adminlte::page')
@section('title', 'Registros Activos')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap">
        <div>
            <h1 class="mb-0">Dentro del Predio (Activos)</h1>
            <small class="text-muted">Personas y vehículos actualmente dentro</small>
        </div>
        <div class="mt-2 mt-md-0 d-flex flex-wrap">
            <a href="{{ route('access.index') }}" class="btn btn-secondary btn-sm mr-2 mb-2">
                <i class="fas fa-list"></i> Ver todos
            </a>
            <a href="{{ route('access.exit.form') }}" class="btn btn-outline-primary btn-sm mb-2">
                <i class="fas fa-sign-out-alt"></i> Registrar salida
            </a>
        </div>
    </div>

    @if($isAdmin ?? false)
        <form method="GET" class="form-row mt-3">
            <div class="col-12 col-md-4 mb-2">
                <select name="branch_id" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">Todas las sucursales</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ (string)request('branch_id') === (string)$branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    @endif
@endsection

@section('content')
    @if(session('success'))
        <x-adminlte-alert theme="success">{{ session('success') }}</x-adminlte-alert>
    @endif

    {{-- DESKTOP --}}
    <div class="d-none d-md-block">
        <div class="table-responsive">
            <table class="table table-striped table-sm align-middle">
                <thead class="thead-light sticky-top">
                    <tr>
                        <th>#</th>
                        <th>Tipo</th>
                        <th>Sucursal</th>
                        <th>Placa</th>
                        <th>Ocupantes dentro</th>
                        <th>Entrada</th>
                        <th>Estado</th>
                        <th style="width:1%;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($accesses as $a)
                    @php
                        $isVehicle   = $a->type === 'vehicle';
                        $insidePeople= $a->relationLoaded('people') ? $a->people : $a->people()->whereNull('exit_at')->get();
                        $insideCount = $insidePeople->count();
                        $vehicleOut  = $isVehicle && !empty($a->vehicle_exit_at);
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
                                    {{ $p->full_name }} @if($p->is_driver)<small class="text-light">(Chofer)</small>@endif
                                </span>
                            @empty
                                <span class="text-muted">—</span>
                            @endforelse
                        </td>
                        <td>{{ $a->entry_at?->timezone('America/Asuncion')->format('d/m/Y H:i') }}</td>
                        <td>
                            <span class="badge badge-warning">Dentro: {{ $insideCount }}</span>
                            @if($vehicleOut)
                                <span class="badge badge-info">Vehículo fuera</span>
                            @endif
                        </td>
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
                    <tr><td colspan="8">No hay personas/vehículos dentro.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $accesses->onEachSide(1)->appends(request()->query())->links('pagination::bootstrap-4') }}
    </div>

    {{-- MÓVIL --}}
    <div class="d-md-none">
        @forelse($accesses as $a)
            @php
                $isVehicle   = $a->type === 'vehicle';
                $insidePeople= $a->relationLoaded('people') ? $a->people : $a->people()->whereNull('exit_at')->get();
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
            {{ $accesses->onEachSide(1)->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection

@push('css')
<style>
    .thead-light.sticky-top { top: 0; z-index: 1; }

    /* Compacto en móvil */
    @media (max-width: 767.98px) {
        .btn { padding: .35rem .6rem; font-size: .9rem; }
        .table { font-size: .9rem; }
    }

    /* Paginación compacta */
    .pagination { margin-bottom: 0; }
    .pagination .page-link { padding: .25rem .5rem; font-size: .875rem; }
    .pagination .page-item { margin: 0 2px; }
</style>
@endpush
