@extends('adminlte::page')

@section('title','Asignaciones')

@section('content_header')
    <h1>Asignaciones</h1>
@endsection

@section('content')
    <x-adminlte-button label="Nueva asignación" theme="primary" icon="fas fa-plus" class="mb-3"
        onclick="window.location='{{ route('admin.patrol.assignments.create') }}'"/>

    <x-adminlte-card>
        <table class="table table-striped" id="assignmentsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Guardia</th>
                    <th>Ruta</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th class="d-none d-md-table-cell">Estado</th>
                    <th class="d-none d-md-table-cell">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assignments as $a)
                    <tr class="assignment-row" data-id="{{ $a->id }}">
                        <td>{{ $a->id }}</td>
                        <td>{{ $a->guardUser->name ?? '-' }}</td>
                        <td>{{ $a->route->name ?? '-' }}</td>
                        <td>{{ $a->scheduled_start }}</td>
                        <td>{{ $a->scheduled_end }}</td>
                        <td class="d-none d-md-table-cell">
                            @php
                                $statusLabels = [
                                    'scheduled' => 'Programada',
                                    'in_progress' => 'En curso',
                                    'completed' => 'Completada',
                                    'missed' => 'Perdida',
                                    'cancelled' => 'Cancelada',
                                ];
                                $status = $statusLabels[$a->status] ?? $a->status;
                            @endphp
                            <span class="badge bg-info">{{ $status }}</span>
                        </td>
                        <td class="d-none d-md-table-cell">
                            <a class="btn btn-sm btn-info" href="{{ route('admin.patrol.assignments.edit',$a) }}">Editar</a>
                            <form action="{{ route('admin.patrol.assignments.destroy',$a) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Eliminar asignación?')">
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
                                    <strong>Estado:</strong> <span class="badge bg-info">{{ $status }}</span>
                                </div>
                                <div>
                                    <strong>Acciones:</strong>
                                    <a class="btn btn-sm btn-info" href="{{ route('admin.patrol.assignments.edit',$a) }}">Editar</a>
                                    <form action="{{ route('admin.patrol.assignments.destroy',$a) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Eliminar asignación?')">
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
        {{ $assignments->links() }}
    </x-adminlte-card>

@endsection

@push('js')
    <script>
        // Agregar interactividad para mostrar las filas con animación en móvil
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('.assignment-row');
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
