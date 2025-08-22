{{-- resources/views/admin/patrol/routes/index.blade.php --}}
@extends('adminlte::page')

@section('title','Rutas de Patrulla')

@section('content_header')
    <h1>Rutas de Patrulla</h1>
@endsection

@section('content')

    @if (session('success'))
        <x-adminlte-alert theme="success" title="OK">
            {{ session('success') }}
        </x-adminlte-alert>
    @endif

    <x-adminlte-button label="Nueva ruta" theme="primary" icon="fas fa-plus" class="mb-3"
        onclick="window.location='{{ route('admin.patrol.routes.create') }}'"/>

    <x-adminlte-card>
        <table class="table table-striped" id="routesTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Sucursal</th>
                    <th>Nombre</th>
                    <th>Duración</th>
                    <th class="d-none d-md-table-cell">Activa</th>
                    <th class="d-none d-md-table-cell">Acciones</th>
                </tr>
            </thead>
            <tbody>
            @foreach($routes as $r)
                <tr class="route-row" data-id="{{ $r->id }}">
                    <td>{{ $r->id }}</td>
                    <td>{{ $r->branch->name ?? '-' }}</td>
                    <td>{{ $r->name }}</td>
                    <td>{{ $r->expected_duration_min }} min</td>
                    <td class="d-none d-md-table-cell">
                        {!! $r->active
                            ? '<span class="badge bg-success">Sí</span>'
                            : '<span class="badge bg-secondary">No</span>' !!}
                    </td>
                    <td class="d-none d-md-table-cell">
                        <a class="btn btn-sm btn-info" href="{{ route('admin.patrol.routes.edit',$r) }}">Editar</a>
                        <a class="btn btn-sm btn-secondary" href="{{ route('admin.patrol.routes.checkpoints.index',$r) }}">Checkpoints</a>
                        <form action="{{ route('admin.patrol.routes.destroy',$r) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Eliminar ruta?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <!-- Filas de Estado y Acciones para móviles -->
                <tr class="additional-info d-none">
                    <td colspan="7">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>Activa:</strong>
                                {!! $r->active
                                    ? '<span class="badge bg-success">Sí</span>'
                                    : '<span class="badge bg-secondary">No</span>' !!}
                            </div>
                            <div>
                                <strong>Acciones:</strong>
                                <a class="btn btn-sm btn-info" href="{{ route('admin.patrol.routes.edit',$r) }}">Editar</a>
                                <a class="btn btn-sm btn-secondary" href="{{ route('admin.patrol.routes.checkpoints.index',$r) }}">Checkpoints</a>
                                <form action="{{ route('admin.patrol.routes.destroy',$r) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Eliminar ruta?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{ $routes->links() }}
    </x-adminlte-card>

@endsection

@push('js')
    <script>
        // Agregar interactividad para mostrar las filas con animación en móvil
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('.route-row');
            rows.forEach(row => {
                row.addEventListener('click', function() {
                    const additionalInfoRow = this.nextElementSibling;
                    const screenWidth = window.innerWidth;

                    // Si es dispositivo móvil (menos de 768px)
                    if (screenWidth <= 768) {
                        // Toggle de la visibilidad con animación
                        additionalInfoRow.classList.toggle('d-none');
                        additionalInfoRow.classList.toggle('slide-down');
                    }
                });
            });
        });
    </script>
@endpush

@push('css')
    <style>
        /* Animación de deslizamiento hacia abajo */
        .slide-down {
            animation: slideDown 0.3s ease-out forwards;
        }

        @keyframes slideDown {
            from {
                max-height: 0;
                opacity: 0;
            }
            to {
                max-height: 200px;
                opacity: 1;
            }
        }
    </style>
@endpush
