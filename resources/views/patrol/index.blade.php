{{-- resources/views/patrol/index.blade.php --}}
@extends('adminlte::page')
@section('title', 'Mis Patrullas')

@push('css')
    <style>
        @media (max-width: 576px) {
            .actions>* {
                margin-bottom: .5rem;
            }
        }

        .badge-status {
            text-transform: capitalize;
        }
    </style>
@endpush

@section('content_header')
    <h1>Mis Patrullas</h1>
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

    @php
        $estadoTranslation = [
            'scheduled' => 'Programada',
            'in_progress' => 'En progreso',
            'completed' => 'Completada',
            'missed' => 'Perdida',
            'cancelled' => 'Cancelada',
        ];
    @endphp

    <x-adminlte-card theme="light" title="Asignaciones">
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ruta</th>
                        <th>Sucursal</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assignments as $a)
                        @php
                            $route = $a->route;
                            $branchName = $route->branch->name ?? '—';
                            // Usar relaciones ya cargadas para evitar N+1
                            $total = $route?->checkpoints?->count() ?? 0;
                            $done = $a->scans?->count() ?? 0;
                            $progress = $total ? intval(min(100, ($done * 100) / $total)) : 0;
                            $clr =
                                [
                                    'scheduled' => 'secondary',
                                    'in_progress' => 'info',
                                    'completed' => 'success',
                                    'missed' => 'warning',
                                    'cancelled' => 'dark',
                                ][$a->status] ?? 'secondary';
                            $estadoEspañol = $estadoTranslation[$a->status] ?? 'Desconocido';
                            $canScan = $a->status !== 'completed' && ($total === 0 || $done < $total);
                        @endphp
                        <tr>
                            <td>{{ $a->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ $route->name ?? '—' }}</div>
                                <div class="small text-muted">Progreso: {{ $done }}/{{ $total }}
                                    ({{ $progress }}%)</div>
                                <div class="progress" style="height:6px; max-width:220px;">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%;"></div>
                                </div>
                            </td>
                            <td>{{ $branchName }}</td>
                            <td>{{ $a->scheduled_start }}</td>
                            <td>{{ $a->scheduled_end }}</td>
                            <td><span class="badge bg-{{ $clr }} badge-status">{{ $estadoEspañol }}</span></td>
                            <td class="text-end">
                                <div class="actions d-flex flex-wrap justify-content-end gap-2">
                                    @if ($canScan)
                                        <a href="{{ route('patrol.scan') }}?a={{ $a->id }}"
                                            class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-qrcode"></i> Escanear
                                        </a>
                                    @else
                                        <button class="btn btn-outline-secondary btn-sm" disabled>
                                            <i class="fas fa-qrcode"></i> Escaneado
                                        </button>
                                    @endif

                                    @if ($a->status === 'scheduled')
                                        <form method="POST" action="{{ route('patrol.start', $a) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-success">
                                                <i class="fas fa-play"></i> Iniciar
                                            </button>
                                        </form>
                                    @endif

                                    @if (in_array($a->status, ['scheduled', 'in_progress']))
                                        <form method="POST" action="{{ route('patrol.finish', $a) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-flag-checkered"></i> Finalizar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No tenés asignaciones.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (method_exists($assignments, 'links'))
            <div class="mt-2">{{ $assignments->links() }}</div>
        @endif
    </x-adminlte-card>
@endsection
