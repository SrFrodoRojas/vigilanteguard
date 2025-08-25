{{-- resources/views/admin/patrol/routes/index.blade.php --}}
@extends('adminlte::page')
@section('title', 'Rutas de Patrulla')

@section('content_header')
    <h1>
        Rutas de Patrulla</h1>
@endsection

@section('content')

    @if (session('success'))
        <x-adminlte-alert theme="success" title="OK">{{ session('success') }}</x-adminlte-alert>
    @endif
    @if (session('warning'))
        <x-adminlte-alert theme="warning" title="Atención">{{ session('warning') }}</x-adminlte-alert>
    @endif
    @if ($errors->any())
        <x-adminlte-alert theme="danger" title="Error">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    <x-adminlte-card theme="light" title="Listado de rutas" icon="fas fa-route">
        <div class="mb-3">
            <a href="{{ route('admin.patrol.routes.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva ruta
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Sucursal</th>
                        <th>Duración esperada</th>
                        <th>Radio mínimo</th>
                        <th>QR requerido</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($routes as $r)
                        @php
                            $badge = $r->active ? 'success' : 'secondary';
                            $label = $r->active ? 'Activo' : 'Inactivo';
                        @endphp
                        <tr>
                            <td>{{ $r->id }}</td>
                            <td class="fw-semibold">{{ $r->name }}</td>
                            <td>{{ $r->branch->name ?? '—' }}</td>
                            <td>{{ $r->expected_duration_min }} min</td>
                            <td>{{ $r->min_radius_m }} m</td>
                            <td>
                                <span class="badge bg-{{ $r->qr_required ? 'info' : 'dark' }}">
                                    {{ $r->qr_required ? 'Sí' : 'No' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $badge }}">{{ $label }}</span>
                            </td>
                            <td class="text-end">
                                @php
                                    // Detectar nombre de ruta existente para administrar checkpoints
                                    if (Route::has('admin.patrol.routes.checkpoints.index')) {
                                        $checkpointsUrl = route('admin.patrol.routes.checkpoints.index', $r);
                                    } elseif (Route::has('admin.patrol.checkpoints.index')) {
                                        $checkpointsUrl = route('admin.patrol.checkpoints.index', ['route' => $r->id]);
                                    } else {
                                        // Fallback sin romper si el nombre varía en tu proyecto
                                        $checkpointsUrl = url('/admin/patrol/routes/' . $r->id . '/checkpoints');
                                    }
                                @endphp

                                <div class="d-flex flex-wrap justify-content-end gap-2">
                                    <a class="btn btn-outline-secondary btn-sm" href="{{ $checkpointsUrl }}">
                                        <i class="fas fa-map-marker-alt"></i> Checkpoints
                                    </a>

                                    <a class="btn btn-outline-primary btn-sm"
                                        href="{{ route('admin.patrol.routes.edit', $r) }}">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No hay rutas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (method_exists($routes, 'links'))
            <div class="mt-2">{{ $routes->links() }}</div>
        @endif
    </x-adminlte-card>
@endsection
