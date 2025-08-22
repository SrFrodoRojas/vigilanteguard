@extends('adminlte::page')
@section('title', 'Registros de Acceso')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap">
        <div>
            <h1 class="mb-0">Registros de Acceso</h1>
            <small class="text-muted">Vehículos y A pie con filtros independientes</small>
        </div>
        <div class="mt-2 mt-md-0 d-flex flex-wrap">
            <a href="{{ route('access.create') }}" class="btn btn-primary btn-sm mr-2 mb-2">
                <i class="fas fa-sign-in-alt"></i> Registrar Entrada
            </a>
            <a href="{{ route('access.active') }}" class="btn btn-outline-warning btn-sm mr-2 mb-2">
                <i class="fas fa-user-check"></i> Ver Activos
            </a>
            <a href="{{ route('access.exit.form') }}" class="btn btn-outline-secondary btn-sm mr-2 mb-2">
                <i class="fas fa-sign-out-alt"></i> Registrar Salida
            </a>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-info btn-sm mb-2">
                <i class="fas fa-chart-bar"></i> Reportes
            </a>
        </div>
    </div>

    {{-- Botones rápidos para saltar a la sección --}}
    <div class="mt-3 d-flex flex-wrap">
        <a href="#vehiculos-section" class="btn btn-outline-primary btn-sm mr-2 mb-2">
            <i class="fas fa-car"></i> Ir a Vehículos
        </a>
        <a href="#a-pie-section" class="btn btn-outline-success btn-sm mb-2">
            <i class="fas fa-walking"></i> Ir a A pie
        </a>
    </div>
@endsection


@section('content')
    @if (session('success'))
        <x-adminlte-alert theme="success" class="mb-3">
            <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
        </x-adminlte-alert>
    @endif

    {{-- ==================== SECCIÓN: VEHÍCULOS ==================== --}}
    <div id="vehiculos-section" class="card shadow-sm mb-4">
        <div class="card-header bg-gradient-primary d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="fas fa-car-alt mr-2"></i>
                <h3 class="card-title mb-0">Vehículos</h3>
                <span class="badge badge-light ml-2">{{ $vehicles->total() }}</span>
            </div>

            {{-- Toggle filtros en móvil --}}
            <button class="btn btn-sm btn-outline-light d-inline d-md-none" type="button" data-toggle="collapse"
                data-target="#vehFilters" aria-expanded="false" aria-controls="vehFilters">
                <i class="fas fa-sliders-h mr-1"></i>Filtros
            </button>
        </div>

        <div class="card-body">
            {{-- Filtros Vehículos --}}
            <form method="GET" class="form-row mb-3 collapse d-md-flex show" id="vehFilters">
                {{-- Mantener parámetros de la otra sección (peatones) al filtrar vehículos --}}
                <input type="hidden" name="q_ped" value="{{ request('q_ped') }}">
                <input type="hidden" name="branch_ped" value="{{ request('branch_ped') }}">
                <input type="hidden" name="status_ped" value="{{ request('status_ped') }}">
                <input type="hidden" name="from_ped" value="{{ request('from_ped') }}">
                <input type="hidden" name="to_ped" value="{{ request('to_ped') }}">
                <input type="hidden" name="ped_page" value="{{ request('ped_page') }}">

                <div class="col-12 col-md-4 mb-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" name="q_veh" class="form-control" placeholder="Nombre / Documento / Placa"
                            value="{{ request('q_veh') }}">
                    </div>
                </div>

                @if ($isAdmin)
                    <div class="col-12 col-md-3 mb-2">
                        <select name="branch_veh" class="form-control form-control-sm">
                            <option value="">Todas las sucursales</option>
                            @foreach ($branches as $b)
                                <option value="{{ $b->id }}"
                                    {{ (string) request('branch_veh') === (string) $b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-6 col-md-2 mb-2">
                    <select name="status_veh" class="form-control form-control-sm">
                        <option value="">Estado (todos)</option>
                        <option value="inside" {{ request('status_veh') === 'inside' ? 'selected' : '' }}>Dentro</option>
                        <option value="closed" {{ request('status_veh') === 'closed' ? 'selected' : '' }}>Cerrado</option>
                        <option value="pending" {{ request('status_veh') === 'pending' ? 'selected' : '' }}>Pendiente
                        </option>
                    </select>
                </div>

                <div class="col-6 col-md-1 mb-2">
                    <input type="date" name="from_veh" class="form-control form-control-sm"
                        value="{{ request('from_veh') }}" title="Desde">
                </div>
                <div class="col-6 col-md-1 mb-2">
                    <input type="date" name="to_veh" class="form-control form-control-sm"
                        value="{{ request('to_veh') }}" title="Hasta">
                </div>

                <div class="col-6 col-md-1 mb-2 text-right">
                    <button type="submit" class="btn btn-primary btn-sm mb-1">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                </div>

                <div class="col-6 col-md-1 mb-2 text-right">
                    <a href="{{ route('access.index') }}" class="btn btn-secondary btn-sm mb-1">
                        <i class="fas fa-sync"></i> Limpiar
                    </a>
                </div>
            </form>

            {{-- ======= LISTA MÓVIL (cards) ======= --}}
            <div class="d-block d-md-none">
                @forelse($vehicles as $a)
                    @php
                        $inside = (int) ($a->inside_count ?? 0);
                        $vehicleOut = !empty($a->vehicle_exit_at);
                        $closed = !empty($a->exit_at);
                        $rowHref =
                            $inside > 0
                                ? ($a->plate
                                    ? route('access.search', ['plate' => $a->plate])
                                    : route('access.search', ['document' => $a->document]))
                                : route('access.show', $a);
                    @endphp
                    <div class="card mb-2">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge badge-primary">Vehículo</span>
                                <small>#{{ $a->id }}</small>
                            </div>
                            <h6 class="mt-2 mb-1">
                                {{ $a->full_name }}
                                <small class="text-muted">({{ $a->document }})</small>
                            </h6>
                            <div class="mb-1">
                                <i class="fas fa-warehouse"></i>
                                {{ $a->branch->name ?? '—' }}
                            </div>
                            @if ($a->plate)
                                <div class="mb-1"><i class="fas fa-car-alt"></i> Placa:
                                    <strong>{{ $a->plate }}</strong>
                                </div>
                            @endif
                            <div class="text-muted">
                                <i class="far fa-clock"></i>
                                In: {{ $a->entry_at?->timezone('America/Asuncion')->format('d/m H:i') }}
                                @if ($a->exit_at)
                                    · Out: {{ $a->exit_at->timezone('America/Asuncion')->format('d/m H:i') }}
                                @endif
                            </div>
                            <div class="mt-2 d-flex flex-wrap align-items-center">
                                @if ($inside > 0)
                                    <span class="badge badge-warning mr-2 mb-1">Dentro: {{ $inside }}</span>
                                @elseif ($closed)
                                    <span class="badge badge-success mr-2 mb-1">Cerrado</span>
                                @else
                                    <span class="badge badge-secondary mr-2 mb-1">Pendiente</span>
                                @endif

                                @if ($vehicleOut)
                                    <span class="badge badge-info mb-1">Vehículo fuera</span>
                                @endif
                            </div>
                            <div class="mt-2">
                                @if ($inside > 0)
                                    <a href="{{ $rowHref }}" class="btn btn-success btn-block">
                                        <i class="fas fa-sign-out-alt"></i> Gestionar salida
                                    </a>
                                @else
                                    <a href="{{ route('access.show', $a) }}" class="btn btn-outline-primary btn-block">
                                        <i class="fas fa-eye"></i> Ver detalle
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <x-adminlte-alert theme="warning">Sin registros.</x-adminlte-alert>
                @endforelse

                <div class="mt-2">
                    {{ $vehicles->onEachSide(1)->appends(request()->except('veh_page'))->links('pagination::bootstrap-4') }}
                </div>
            </div>

            {{-- ======= TABLA DESKTOP ======= --}}
            <div class="table-responsive d-none d-md-block">
                <table class="table table-striped table-hover align-middle" id="veh-table">
                    <thead class="thead-light sticky-top">
                        <tr>
                            <th>#</th>
                            <th>Sucursal</th>
                            <th>Placa</th>
                            <th>Nombre</th>
                            <th>Documento</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Estado</th>
                            <th class="text-nowrap text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vehicles as $a)
                            @php
                                $inside = (int) ($a->inside_count ?? 0);
                                $vehicleOut = !empty($a->vehicle_exit_at);
                                $closed = !empty($a->exit_at);
                                $rowHref =
                                    $inside > 0
                                        ? ($a->plate
                                            ? route('access.search', ['plate' => $a->plate])
                                            : route('access.search', ['document' => $a->document]))
                                        : route('access.show', $a);
                            @endphp
                            <tr class="row-link" data-href="{{ $rowHref }}">
                                <td>{{ $a->id }}</td>
                                <td>{{ $a->branch->name ?? '—' }}</td>
                                <td>{{ $a->plate ?? '—' }}</td>
                                <td>{{ $a->full_name }}</td>
                                <td>{{ $a->document }}</td>
                                <td>{{ $a->entry_at?->timezone('America/Asuncion')->format('d/m/Y H:i') }}</td>
                                <td>{{ $a->exit_at?->timezone('America/Asuncion')->format('d/m/Y H:i') ?? '—' }}</td>
                                <td>
                                    @if ($inside > 0)
                                        <span class="badge badge-warning">Dentro: {{ $inside }}</span>
                                        @if ($vehicleOut)
                                            <span class="badge badge-info">Vehículo fuera</span>
                                        @endif
                                    @elseif($closed)
                                        <span class="badge badge-success">Cerrado</span>
                                    @else
                                        <span class="badge badge-secondary">Pendiente</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if ($inside > 0)
                                        <a href="{{ $rowHref }}" class="btn btn-sm btn-success"
                                            onclick="event.stopPropagation();">
                                            <i class="fas fa-sign-out-alt"></i> Salida
                                        </a>
                                    @else
                                        <a href="{{ route('access.show', $a) }}" class="btn btn-sm btn-outline-primary"
                                            onclick="event.stopPropagation();">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">Sin registros.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $vehicles->onEachSide(1)->appends(request()->except('veh_page'))->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    {{-- ==================== SECCIÓN: A PIE (PEATONES) ==================== --}}
    <div id="a-pie-section" class="card shadow-sm">
        <div class="card-header bg-gradient-success d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="fas fa-walking mr-2"></i>
                <h3 class="card-title mb-0">A pie</h3>
                <span class="badge badge-light ml-2">{{ $pedestrians->total() }}</span>
            </div>

            {{-- Toggle filtros móvil --}}
            <button class="btn btn-sm btn-outline-light d-inline d-md-none" type="button" data-toggle="collapse"
                data-target="#pedFilters" aria-expanded="false" aria-controls="pedFilters">
                <i class="fas fa-sliders-h mr-1"></i>Filtros
            </button>
        </div>

        <div class="card-body">
            {{-- Filtros Peatones --}}
            <form method="GET" class="form-row mb-3 collapse d-md-flex show" id="pedFilters">
                {{-- Mantener parámetros de la otra sección (vehículos) al filtrar peatones --}}
                <input type="hidden" name="q_veh" value="{{ request('q_veh') }}">
                <input type="hidden" name="branch_veh" value="{{ request('branch_veh') }}">
                <input type="hidden" name="status_veh" value="{{ request('status_veh') }}">
                <input type="hidden" name="from_veh" value="{{ request('from_veh') }}">
                <input type="hidden" name="to_veh" value="{{ request('to_veh') }}">
                <input type="hidden" name="veh_page" value="{{ request('veh_page') }}">

                <div class="col-12 col-md-4 mb-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" name="q_ped" class="form-control" placeholder="Nombre / Documento"
                            value="{{ request('q_ped') }}">
                    </div>
                </div>

                @if ($isAdmin)
                    <div class="col-12 col-md-3 mb-2">
                        <select name="branch_ped" class="form-control form-control-sm">
                            <option value="">Todas las sucursales</option>
                            @foreach ($branches as $b)
                                <option value="{{ $b->id }}"
                                    {{ (string) request('branch_ped') === (string) $b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-6 col-md-2 mb-2">
                    <select name="status_ped" class="form-control form-control-sm">
                        <option value="">Estado (todos)</option>
                        <option value="inside" {{ request('status_ped') === 'inside' ? 'selected' : '' }}>Dentro</option>
                        <option value="closed" {{ request('status_ped') === 'closed' ? 'selected' : '' }}>Cerrado</option>
                        <option value="pending" {{ request('status_ped') === 'pending' ? 'selected' : '' }}>Pendiente
                        </option>
                    </select>
                </div>

                <div class="col-6 col-md-1 mb-2">
                    <input type="date" name="from_ped" class="form-control form-control-sm"
                        value="{{ request('from_ped') }}" title="Desde">
                </div>
                <div class="col-6 col-md-1 mb-2">
                    <input type="date" name="to_ped" class="form-control form-control-sm"
                        value="{{ request('to_ped') }}" title="Hasta">
                </div>

                <div class="col-6 col-md-1 mb-2 text-right">
                    <button type="submit" class="btn btn-primary btn-sm mb-1">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                </div>

                <div class="col-6 col-md-1 mb-2 text-right">
                    <a href="{{ route('access.index') }}" class="btn btn-secondary btn-sm mb-1">
                        <i class="fas fa-sync"></i> Limpiar
                    </a>
                </div>
            </form>

            {{-- ======= LISTA MÓVIL (cards) ======= --}}
            <div class="d-block d-md-none">
                @forelse($pedestrians as $a)
                    @php
                        $inside = (int) ($a->inside_count ?? 0);
                        $closed = !empty($a->exit_at);
                        $rowHref =
                            $inside > 0
                                ? route('access.search', ['document' => $a->document])
                                : route('access.show', $a);
                    @endphp
                    <div class="card mb-2">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge badge-success">A pie</span>
                                <small>#{{ $a->id }}</small>
                            </div>
                            <h6 class="mt-2 mb-1">
                                {{ $a->full_name }}
                                <small class="text-muted">({{ $a->document }})</small>
                            </h6>
                            <div class="mb-1">
                                <i class="fas fa-warehouse"></i>
                                {{ $a->branch->name ?? '—' }}
                            </div>
                            <div class="text-muted">
                                <i class="far fa-clock"></i>
                                In: {{ $a->entry_at?->timezone('America/Asuncion')->format('d/m H:i') }}
                                @if ($a->exit_at)
                                    · Out: {{ $a->exit_at->timezone('America/Asuncion')->format('d/m H:i') }}
                                @endif
                            </div>
                            <div class="mt-2">
                                @if ($inside > 0)
                                    <a href="{{ $rowHref }}" class="btn btn-success btn-block">
                                        <i class="fas fa-sign-out-alt"></i> Gestionar salida
                                    </a>
                                @else
                                    <a href="{{ route('access.show', $a) }}" class="btn btn-outline-primary btn-block">
                                        <i class="fas fa-eye"></i> Ver detalle
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <x-adminlte-alert theme="warning">Sin registros.</x-adminlte-alert>
                @endforelse

                <div class="mt-2">
                    {{ $pedestrians->onEachSide(1)->appends(request()->except('ped_page'))->links('pagination::bootstrap-4') }}
                </div>
            </div>

            {{-- ======= TABLA DESKTOP ======= --}}
            <div class="table-responsive d-none d-md-block">
                <table class="table table-striped table-hover align-middle" id="ped-table">
                    <thead class="thead-light sticky-top">
                        <tr>
                            <th>#</th>
                            <th>Sucursal</th>
                            <th>Nombre</th>
                            <th>Documento</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Estado</th>
                            <th class="text-nowrap text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pedestrians as $a)
                            @php
                                $inside = (int) ($a->inside_count ?? 0);
                                $closed = !empty($a->exit_at);
                                $rowHref =
                                    $inside > 0
                                        ? route('access.search', ['document' => $a->document])
                                        : route('access.show', $a);
                            @endphp
                            <tr class="row-link" data-href="{{ $rowHref }}">
                                <td>{{ $a->id }}</td>
                                <td>{{ $a->branch->name ?? '—' }}</td>
                                <td>{{ $a->full_name }}</td>
                                <td>{{ $a->document }}</td>
                                <td>{{ $a->entry_at?->timezone('America/Asuncion')->format('d/m/Y H:i') }}</td>
                                <td>{{ $a->exit_at?->timezone('America/Asuncion')->format('d/m/Y H:i') ?? '—' }}</td>
                                <td>
                                    @if ($inside > 0)
                                        <span class="badge badge-warning">Dentro: {{ $inside }}</span>
                                    @elseif($closed)
                                        <span class="badge badge-success">Cerrado</span>
                                    @else
                                        <span class="badge badge-secondary">Pendiente</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if ($inside > 0)
                                        <a href="{{ $rowHref }}" class="btn btn-sm btn-success"
                                            onclick="event.stopPropagation();">
                                            <i class="fas fa-sign-out-alt"></i> Salida
                                        </a>
                                    @else
                                        <a href="{{ route('access.show', $a) }}" class="btn btn-sm btn-outline-primary"
                                            onclick="event.stopPropagation();">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">Sin registros.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $pedestrians->onEachSide(1)->appends(request()->except('ped_page'))->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        .thead-light.sticky-top {
            top: 0;
            z-index: 1;
        }

        #veh-table tr.row-link,
        #ped-table tr.row-link {
            cursor: pointer;
        }

        /* Cards móviles compactas */
        @media (max-width: 767.98px) {
            .btn {
                padding: .35rem .6rem;
                font-size: .9rem;
            }

            .input-group-text,
            .form-control-sm {
                font-size: .9rem;
            }
        }

        /* Paginación compacta */
        .pagination {
            margin-bottom: 0;
        }

        .pagination .page-link {
            padding: .25rem .5rem;
            font-size: .875rem;
        }

        .pagination .page-item {
            margin: 0 2px;
        }

        /* Compactar UI en móvil */
        @media (max-width: 767.98px) {
            .btn {
                padding: .3rem .5rem;
                font-size: .85rem;
            }

            .form-control,
            .form-control-sm,
            .input-group-text {
                font-size: .85rem;
            }

            .card-body {
                padding: .85rem;
            }

            .table {
                font-size: .9rem;
            }
        }

        /* Desplazamiento suave al hacer click en los botones rápidos */
        html {
            scroll-behavior: smooth;
        }

        /* Evita que el título quede tapado por navbar/sticky cuando saltás con el anchor */
        #vehiculos-section,
        #a-pie-section {
            scroll-margin-top: 80px;
        }
    </style>
@endpush

@push('js')
    <script>
        // Click fila => ir al detalle/gestión (respetando clicks en botones)
        ;
        ['veh-table', 'ped-table'].forEach(id => {
            document.querySelectorAll(`#${id} tr.row-link`).forEach(tr => {
                tr.addEventListener('click', (e) => {
                    if (e.target.closest('a,button,input,label')) return;
                    const href = tr.dataset.href;
                    if (href) window.location.assign(href);
                });
            });
        });
    </script>
@endpush
