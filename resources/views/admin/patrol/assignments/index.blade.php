{{-- resources/views/admin/patrol/assignments/index.blade.php --}}
@extends('adminlte::page')
@section('title', 'Asignaciones')

@section('content_header')
    <h1>Asignaciones</h1>
@endsection

@section('content')
    {{-- FLASH / VALIDACIÓN mejorados --}}
    @php
        $flashMap = [
            'success' => ['theme' => 'success', 'title' => 'OK'],
            'warning' => ['theme' => 'warning', 'title' => 'Atención'],
            'info' => ['theme' => 'info', 'title' => 'Info'],
            'error' => ['theme' => 'danger', 'title' => 'Error'],
            'status' => ['theme' => 'info', 'title' => 'Estado'],
        ];
    @endphp

    @foreach ($flashMap as $key => $cfg)
        @if (session()->has($key))
            <x-adminlte-alert :theme="$cfg['theme']" :title="$cfg['title']">
                {{ session($key) }}
            </x-adminlte-alert>
        @endif
    @endforeach

    @if ($errors->any())
        <x-adminlte-alert theme="danger" title="Error de validación">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    <x-adminlte-card>
        {{-- CTA: botón crear (stack en móvil) --}}
        <div class="mb-3 d-grid d-md-inline-block">
            <a href="{{ route('admin.patrol.assignments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva asignación
            </a>
        </div>

        @php
            $statusLabels = [
                'scheduled' => 'Programada',
                'in_progress' => 'En curso',
                'completed' => 'Completada',
                'missed' => 'Perdida',
                'cancelled' => 'Cancelada',
            ];
            $statusColors = [
                'scheduled' => 'secondary',
                'in_progress' => 'info',
                'completed' => 'success',
                'missed' => 'warning',
                'cancelled' => 'dark',
            ];
        @endphp

        {{-- DESKTOP: Tabla (>= md) --}}
        <div class="table-responsive d-none d-md-block">
            <table class="table table-striped align-middle" id="assignmentsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Guardia</th>
                        <th>Ruta</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $a)
                        @php
                            $statusKey = $a->status;
                            $statusTxt = $statusLabels[$statusKey] ?? $statusKey;
                            $statusClr = $statusColors[$statusKey] ?? 'secondary';
                        @endphp
                        <tr>
                            <td>{{ $a->id }}</td>
                            <td>{{ $a->guardUser->name ?? '—' }}</td>
                            <td>{{ $a->route->name ?? '—' }}</td>
                            <td>{{ $a->scheduled_start }}</td>
                            <td>{{ $a->scheduled_end }}</td>
                            <td><span class="badge bg-{{ $statusClr }}">{{ $statusTxt }}</span></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary"
                                    href="{{ route('admin.patrol.assignments.edit', $a) }}">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <form action="{{ route('admin.patrol.assignments.destroy', $a) }}" method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('¿Eliminar asignación #{{ $a->id }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No hay asignaciones.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- MÓVIL: Cards (< md) --}}
        <div class="d-md-none">
            @forelse($assignments as $a)
                @php
                    $statusKey = $a->status;
                    $statusTxt = $statusLabels[$statusKey] ?? $statusKey;
                    $statusClr = $statusColors[$statusKey] ?? 'secondary';
                @endphp

                <div class="card mb-3 assignment-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="small text-muted">#{{ $a->id }}</div>
                                <h5 class="card-title mb-0">{{ $a->route->name ?? '—' }}</h5>
                                <div class="small text-muted">{{ $a->guardUser->name ?? '—' }}</div>
                            </div>
                            <span class="badge bg-{{ $statusClr }} ms-2">{{ $statusTxt }}</span>
                        </div>

                        <div class="kv mb-3">
                            <div class="text-muted">Inicio</div>
                            <div>{{ $a->scheduled_start }}</div>

                            <div class="text-muted">Fin</div>
                            <div>{{ $a->scheduled_end }}</div>
                        </div>

                        <div class="d-flex gap-2 actions flex-column flex-sm-row">
                            <a class="btn btn-outline-primary" href="{{ route('admin.patrol.assignments.edit', $a) }}">
                                <i class="fas fa-edit"></i> Editar
                            </a>

                            <form action="{{ route('admin.patrol.assignments.destroy', $a) }}" method="POST"
                                onsubmit="return confirm('¿Eliminar asignación #{{ $a->id }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger">
                                    <i class="fas fa-trash-alt"></i> Eliminar
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-3">No hay asignaciones.</div>
            @endforelse
        </div>

        @if (method_exists($assignments, 'links'))
            <div class="mt-2">{{ $assignments->links() }}</div>
        @endif
    </x-adminlte-card>
@endsection

@push('css')
    <style>
        /* Cards solo en móviles */
        @media (max-width: 767.98px) {
            .assignment-card .kv {
                display: grid;
                grid-template-columns: 120px 1fr;
                gap: .25rem .5rem;
            }

            .assignment-card .actions>* {
                width: 100%;
            }
        }

        /* Hace que cada hijo dentro de .actions tenga el mismo ancho */
        .actions>* {
            flex: 1 1 0;
        }

        /* El botón dentro del form llena el ancho de su contenedor */
        .actions form .btn,
        .actions>.btn {
            width: 100%;
            margin-top: 3%; /* separa un poco del elemento de arriba */
        }
    </style>
@endpush
