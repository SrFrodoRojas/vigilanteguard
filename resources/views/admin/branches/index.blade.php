@extends('adminlte::page')

@section('title', 'Sucursales')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap">
        <div>
            <h1 class="mb-0">Sucursales</h1>
            <small class="text-muted">Gestión de sedes, encargados y guardias asignados</small>
        </div>
        <a href="{{ route('branches.create') }}" class="btn btn-primary mt-2 mt-md-0">
            <i class="fas fa-plus"></i> Nueva Sucursal
        </a>
    </div>
@endsection

@section('content')
    @if (session('success'))
        <x-adminlte-alert theme="success" class="mb-3">
            <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
        </x-adminlte-alert>
    @endif

    @if ($errors->any())
        <x-adminlte-alert theme="danger" class="mb-3" title="Revisa los datos">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    {{-- KPIs rápidos --}}
    @php
        $totalBranches    = $branches->count();
        $withManager      = $branches->whereNotNull('manager_id')->count();
        $totalGuards      = $branches->sum('users_count');
    @endphp
    <div class="row mb-2">
        <div class="col-12 col-sm-6 col-lg-4">
            <x-adminlte-small-box title="{{ $totalBranches }}" text="Sucursales" icon="fas fa-building" theme="primary"/>
        </div>
        <div class="col-12 col-sm-6 col-lg-4">
            <x-adminlte-small-box title="{{ $withManager }}" text="Con encargado" icon="fas fa-user-tie" theme="info"/>
        </div>
        <div class="col-12 col-sm-6 col-lg-4">
            <x-adminlte-small-box title="{{ $totalGuards }}" text="Guardias asignados" icon="fas fa-user-shield" theme="success"/>
        </div>
    </div>

    {{-- ======= Vista Móvil (cards) ======= --}}
    <div class="d-md-none">
        @forelse ($branches as $branch)
            @php
                $hue   = ($branch->id * 47) % 360;
                $bg    = $branch->color ?: "hsl($hue, 70%, 92%)";
                $left  = $branch->color ?: "hsl($hue, 70%, 45%)";
                $mgr   = $branch->manager;
                $phone = $mgr?->phone;
                $digits = $phone ? preg_replace('/\D+/', '', $phone) : null;
            @endphp

            <div class="card mb-2 branch-card" style="border-left: .4rem solid {{ $left }}">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="font-weight-bold">{{ $branch->name }}</div>
                            <div class="small text-muted">
                                <i class="fas fa-map-marker-alt"></i> {{ $branch->location }}
                            </div>
                        </div>
                        <span class="badge badge-light text-nowrap">
                            <i class="fas fa-users mr-1"></i>{{ $branch->users_count ?? 0 }}
                        </span>
                    </div>

                    <div class="mt-2">
                        <span class="badge" style="background: {{ $bg }}; color:#333;">Color</span>
                        @if($mgr)
                            <div class="mt-2 small">
                                <strong>Encargado:</strong> {{ $mgr->name }}
                                <div class="text-muted">{{ $mgr->email }}</div>
                                @if($phone)
                                    <div class="mt-1">
                                        <a href="tel:{{ $digits ?: $phone }}" class="mr-2">
                                            <i class="fas fa-phone"></i> {{ $phone }}
                                        </a>
                                        @if($digits)
                                            <a href="https://wa.me/{{ $digits }}" target="_blank" class="text-success">
                                                <i class="fab fa-whatsapp"></i> WhatsApp
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="small text-muted mt-2"><em>Sin encargado asignado</em></div>
                        @endif
                    </div>

                    <div class="mt-3 d-flex">
                        <a href="{{ route('branches.edit', $branch) }}" class="btn btn-sm btn-warning mr-2">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <form action="{{ route('branches.destroy', $branch) }}" method="POST"
                              onsubmit="return confirm('¿Eliminar esta sucursal?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <x-adminlte-alert theme="info">No hay sucursales registradas.</x-adminlte-alert>
        @endforelse
    </div>

    {{-- ======= Vista Desktop (tabla + DataTables) ======= --}}
    <div class="card shadow-sm d-none d-md-block">
        <div class="card-header bg-gradient-primary d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="fas fa-building mr-2"></i>
                <h3 class="card-title mb-0">Listado de Sucursales</h3>
                <span class="badge badge-light ml-2">{{ $totalBranches }}</span>
            </div>
            <small class="text-white-50">Tip: Clic en el ícono de WhatsApp para chatear con el encargado</small>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="branchesTable" class="table table-striped table-hover table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:60px">#</th>
                            <th>Nombre</th>
                            <th>Ubicación</th>
                            <th>Encargado</th>
                            <th class="text-nowrap">Teléfono</th>
                            <th class="text-center">Guardias</th>
                            <th class="text-center">Color</th>
                            <th class="text-right" style="width:120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($branches as $branch)
                            @php
                                $hue   = ($branch->id * 47) % 360;
                                $tone  = $branch->color ?: "hsl($hue, 70%, 92%)";
                                $left  = $branch->color ?: "hsl($hue, 70%, 45%)";
                                $mgr   = $branch->manager;
                                $phone = $mgr?->phone;
                                $digits = $phone ? preg_replace('/\D+/', '', $phone) : null;
                            @endphp
                            <tr class="align-middle branch-row">
                                <td>
                                    <span class="d-inline-block rounded" title="Color de sucursal"
                                          style="width:14px;height:14px;background: {{ $left }}"></span>
                                    <span class="ml-1">{{ $branch->id }}</span>
                                </td>
                                <td class="font-weight-600">{{ $branch->name }}</td>
                                <td>{{ $branch->location }}</td>
                                <td>
                                    @if($mgr)
                                        <div>{{ $mgr->name }}</div>
                                        <div class="small text-muted">{{ $mgr->email }}</div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    @if($phone)
                                        <a href="tel:{{ $digits ?: $phone }}" class="mr-2">
                                            <i class="fas fa-phone"></i> {{ $phone }}
                                        </a>
                                        @if($digits)
                                            <a href="https://wa.me/{{ $digits }}" target="_blank" class="text-success" title="WhatsApp">
                                                <i class="fab fa-whatsapp"></i>
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light">{{ $branch->users_count ?? 0 }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge" style="background: {{ $tone }}; color:#333;">&nbsp;&nbsp;</span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('branches.edit', $branch) }}" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('branches.destroy', $branch) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar esta sucursal?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Ubicación</th>
                            <th>Encargado</th>
                            <th>Teléfono</th>
                            <th class="text-center">Guardias</th>
                            <th class="text-center">Color</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('css')
<style>
    .font-weight-600 { font-weight: 600; }

    /* Card móvil estética */
    .branch-card { border-radius: .6rem; }

    /* Compacto en móvil */
    @media (max-width: 767.98px) {
        .btn { padding: .35rem .6rem; font-size: .9rem; }
        .form-control, .form-control-sm { font-size: .9rem; }
        .card-body { padding: .85rem; }
        .table { font-size: .9rem; }
    }
</style>
@endpush

@section('js')
<script>
    $(function () {
        // DataTables solo en desktop (la tabla ya está oculta en móvil)
        $('#branchesTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 25,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
            },
            columnDefs: [
                { orderable: false, targets: [7] } // acciones
            ]
        });
    });
</script>
@endsection
