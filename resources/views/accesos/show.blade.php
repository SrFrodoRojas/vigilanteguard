@extends('adminlte::page')
@section('title', 'Detalle de Acceso')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap">
        <div>
            <h1 class="mb-0">Detalle de acceso #{{ $access->id }}</h1>
            <small class="text-muted">
                {{ $access->type === 'vehicle' ? 'Vehículo' : 'A pie' }}
                @if($access->branch)
                    · Sucursal: <span class="badge badge-info">{{ $access->branch->name }}</span>
                @endif
            </small>
        </div>
        <div class="mt-2 mt-md-0 d-flex flex-wrap">
            <a href="{{ route('access.index') }}" class="btn btn-secondary btn-sm mr-2 mb-2">
                <i class="fas fa-arrow-left"></i> Volver a listados
            </a>
            <a href="{{ route('access.active') }}" class="btn btn-outline-warning btn-sm mr-2 mb-2">
                <i class="fas fa-user-check"></i> Ver Activos
            </a>
            <a href="{{ route('access.exit.form') }}" class="btn btn-outline-primary btn-sm mb-2">
                <i class="fas fa-sign-out-alt"></i> Registrar salida
            </a>
        </div>
    </div>
@endsection

@section('content')
    {{-- INFO GENERAL --}}
    <x-adminlte-card theme="light" icon="fas fa-info-circle" title="Información general" class="mb-3">
        <div class="row">
            <div class="col-12 col-md-6">
                <p class="mb-1">
                    <strong>Tipo:</strong> {{ $access->type === 'vehicle' ? 'Vehículo' : 'A pie' }}
                </p>

                @if ($access->type === 'vehicle')
                    <p class="mb-1"><strong>Placa:</strong> {{ $access->plate ?: '—' }}</p>
                    <p class="mb-1"><strong>Marca:</strong> {{ $access->marca_vehiculo ?? ($access->vehicle_make ?? '—') }}</p>
                    <p class="mb-1"><strong>Color:</strong> {{ $access->color_vehiculo ?? ($access->vehicle_color ?? '—') }}</p>
                    <p class="mb-1"><strong>Tipo de vehículo:</strong> {{ $access->tipo_vehiculo ?? ($access->vehicle_type ?? '—') }}</p>
                @endif

                <p class="mb-1"><strong>Registró:</strong> {{ $access->user?->name ?? '—' }}</p>
                @if($access->branch)
                    <p class="mb-1"><strong>Sucursal:</strong> {{ $access->branch->name }}</p>
                @endif
            </div>

            <div class="col-12 col-md-6">
                <p class="mb-1"><strong>Entrada:</strong> {{ $access->entry_at?->timezone('America/Asuncion')->format('d/m/Y H:i') }}</p>
                <p class="mb-1"><strong>Salida:</strong> {{ $access->exit_at?->timezone('America/Asuncion')->format('d/m/Y H:i') ?? '—' }}</p>
                <p class="mb-1">
                    <strong>Estado:</strong>
                    @if ($insideCount > 0)
                        <span class="badge badge-warning">Dentro: {{ $insideCount }}</span>
                        @if ($access->type === 'vehicle' && $access->vehicle_exit_at)
                            <span class="badge badge-info">Vehículo fuera</span>
                        @endif
                    @else
                        <span class="badge badge-success">Cerrado</span>
                    @endif
                </p>
            </div>
        </div>

        @if ($access->entry_note)
            <p class="mt-2"><strong>Obs. entrada:</strong> {{ $access->entry_note }}</p>
        @endif
        @if ($access->exit_note)
            <p class="mt-1"><strong>Obs. salida:</strong> {{ $access->exit_note }}</p>
        @endif
    </x-adminlte-card>

    {{-- CONDUCCIÓN (solo vehículos) --}}
    @if ($access->type === 'vehicle')
        <x-adminlte-card theme="info" icon="fas fa-car" title="Conducción" class="mb-3">
            <div class="row">
                <div class="col-12 col-md-6">
                    <p class="mb-1"><strong>Entró conduciendo:</strong>
                        {{ $driverEntry?->full_name }} ({{ $driverEntry?->document }})
                    </p>
                </div>
                <div class="col-12 col-md-6">
                    <p class="mb-1"><strong>Salió conduciendo:</strong>
                        @if ($access->vehicle_exit_at && $driverExit)
                            {{ $driverExit->full_name }} ({{ $driverExit->document }})
                        @elseif($access->vehicle_exit_at)
                            No especificado
                        @else
                            Vehículo aún dentro
                        @endif
                    </p>
                </div>
            </div>
        </x-adminlte-card>
    @endif

    {{-- PERSONAS --}}
    <x-adminlte-card theme="secondary" icon="fas fa-users" title="Personas (entrada/salida)">
        {{-- Mobile: lista de chips --}}
        <div class="d-md-none mb-2">
            @foreach ($access->people as $p)
                <div class="border rounded p-2 mb-2">
                    <div class="d-flex justify-content-between">
                        <strong>{{ $p->full_name }}</strong>
                        <span class="badge {{ $p->exit_at ? 'badge-success' : 'badge-warning' }}">
                            {{ $p->exit_at ? 'Salió' : 'Dentro' }}
                        </span>
                    </div>
                    <div class="small text-muted">{{ $p->document }} ·
                        @if ($p->is_driver) Chofer
                        @elseif ($p->role === 'passenger') Acompañante
                        @else Peatón
                        @endif
                    </div>
                    <div class="mt-1 small">
                        <i class="far fa-clock"></i>
                        In: {{ $p->entry_at?->timezone('America/Asuncion')->format('d/m/Y H:i') }}
                        @if ($p->exit_at)
                            · Out: {{ $p->exit_at->timezone('America/Asuncion')->format('d/m/Y H:i') }}
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Desktop: tabla compacta --}}
        <div class="table-responsive d-none d-md-block">
            <table class="table table-striped table-sm align-middle">
                <thead class="thead-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Documento</th>
                        <th>Rol</th>
                        <th>Sexo/Género</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($access->people as $p)
                        <tr>
                            <td>{{ $p->full_name }}</td>
                            <td>{{ $p->document }}</td>
                            <td>
                                @if ($p->is_driver)
                                    Chofer
                                @elseif($p->role === 'passenger')
                                    Acompañante
                                @else
                                    Peatón
                                @endif
                            </td>
                            <td>{{ $p->gender ?? '—' }}</td>
                            <td>{{ $p->entry_at?->timezone('America/Asuncion')->format('d/m/Y H:i') }}</td>
                            <td>{{ $p->exit_at?->timezone('America/Asuncion')->format('d/m/Y H:i') ?? '—' }}</td>
                            <td>
                                @if ($p->exit_at)
                                    <span class="badge badge-success">Salió</span>
                                @else
                                    <span class="badge badge-warning">Dentro</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-adminlte-card>

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('access.index') }}" class="btn btn-secondary btn-sm mr-2 mb-2">
            <i class="fas fa-arrow-left"></i> Volver a listados
        </a>
        <a href="{{ route('access.exit.form') }}" class="btn btn-outline-primary btn-sm mb-2">
            <i class="fas fa-sign-out-alt"></i> Registrar salida
        </a>
    </div>
@endsection

@push('css')
<style>
    /* Compactar UI en móvil */
    @media (max-width: 767.98px) {
        .btn { padding: .35rem .6rem; font-size: .9rem; }
        .card .table { font-size: .9rem; }
    }
</style>
@endpush
