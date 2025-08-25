{{-- resources/views/patrol/index.blade.php --}}
@extends('adminlte::page')
@section('title', 'Mis Patrullas')

@push('css')
<style>
  /* --- Responsive table/cards toggle --- */
  @media (max-width: 767.98px) {
    .desktop-table { display: none !important; }
    .mobile-cards { display: grid; grid-template-columns: 1fr; gap: .75rem; }
    .actions > * { margin-bottom: .5rem; } /* tu regla original para móvil */
  }
  @media (min-width: 768px) {
    .desktop-table { display: block !important; }
    .mobile-cards { display: none !important; }
  }

  /* --- Cards móviles táctiles --- */
  .vg-card {
    border: 1px solid rgba(0,0,0,.08);
    border-radius: .5rem;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
  }
  .vg-card .vg-header {
    display:flex; justify-content:space-between; align-items:center;
    padding:.75rem .75rem .25rem .75rem;
  }
  .vg-card .vg-sub {
    padding: 0 .75rem .5rem .75rem; color:#6c757d; font-size:.9rem;
  }
  .vg-card .vg-body { padding: .25rem .75rem .75rem .75rem; }
  .vg-row {
    display:flex; justify-content:space-between; align-items:center;
    padding:.4rem 0; border-bottom:1px dashed rgba(0,0,0,.06);
  }
  .vg-row:last-child { border-bottom:0; }
  .vg-label { font-weight:600; color:#6c757d; margin-right:.5rem; }
  .vg-value { text-align:right; word-break:break-word; }
  .vg-actions { display:grid; grid-template-columns:1fr 1fr; gap:.5rem; padding:.75rem; }
  .vg-actions .btn { width:100%; }
  .progress.progress-sm { height:6px; }
  .badge-status { text-transform: capitalize; } /* tu regla original */
</style>
@endpush

@section('content_header')
  <h1>Mis Patrullas</h1>
@endsection

@section('content')
  {{-- Bloque de mensajes: success, warning, info, error + validación --}}
  @if (session('success'))
    <x-adminlte-alert theme="success" title="Éxito" class="mb-2" dismissable>
      {{ session('success') }}
    </x-adminlte-alert>
  @endif

  @if (session('warning'))
    <x-adminlte-alert theme="warning" title="Atención" class="mb-2" dismissable>
      {{ session('warning') }}
    </x-adminlte-alert>
  @endif>

  @if (session('info'))
    <x-adminlte-alert theme="info" title="Info" class="mb-2" dismissable>
      {{ session('info') }}
    </x-adminlte-alert>
  @endif

  @if (session('error'))
    <x-adminlte-alert theme="danger" title="Error" class="mb-2" dismissable>
      {{ session('error') }}
    </x-adminlte-alert>
  @endif

  @if ($errors->any())
    <x-adminlte-alert theme="danger" title="Errores de validación" class="mb-3">
      <ul class="mb-0 pl-3">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </x-adminlte-alert>
  @endif

  @php
    $estadoTranslation = [
        'scheduled'   => 'Programada',
        'in_progress' => 'En progreso',
        'completed'   => 'Completada',
        'missed'      => 'Perdida',
        'cancelled'   => 'Cancelada',
    ];
  @endphp

  <x-adminlte-card theme="light" title="Asignaciones">
    {{-- ===== Desktop: Tabla ===== --}}
    <div class="desktop-table">
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

                // Conteos precargados desde el controlador
                $done = (int) ($a->scans_count ?? 0);
                $totalFromSnapshot = (int) ($a->checkpoints_total ?? 0);

                // Fallback compat
                if ($totalFromSnapshot === 0) {
                  if ($a->status === 'completed') {
                    $total = max(1, $done);
                  } else {
                    $total = (int) ($route?->checkpoints?->count() ?? 0);
                  }
                } else {
                  $total = $totalFromSnapshot;
                }

                $progress = $total ? intval(min(100, ($done * 100) / $total)) : 0;

                $clr = [
                  'scheduled'   => 'secondary',
                  'in_progress' => 'info',
                  'completed'   => 'success',
                  'missed'      => 'warning',
                  'cancelled'   => 'dark',
                ][$a->status] ?? 'secondary';

                $estadoEspañol = $estadoTranslation[$a->status] ?? 'Desconocido';

                $canScan = in_array($a->status, ['scheduled', 'in_progress']) && ($total === 0 || $done < $total);
              @endphp

              <tr>
                <td>{{ $a->id }}</td>
                <td>
                  <div class="fw-semibold">{{ $route->name ?? '—' }}</div>
                  <div class="small text-muted">Progreso: {{ $done }}/{{ $total }} ({{ $progress }}%)</div>
                  <div class="progress progress-sm" style="max-width:220px;">
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
                         class="btn btn-outline-primary btn-sm"
                         title="Escanear patrulla #{{ $a->id }}" aria-label="Escanear patrulla {{ $a->id }}">
                        <i class="fas fa-qrcode"></i> Escanear
                      </a>
                    @else
                      <button class="btn btn-outline-secondary btn-sm" disabled title="Escaneo deshabilitado">
                        <i class="fas fa-qrcode"></i> Escaneado
                      </button>
                    @endif

                    @if ($a->status === 'scheduled')
                      <form method="POST" action="{{ route('patrol.start', $a) }}" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-success" title="Iniciar patrulla #{{ $a->id }}" aria-label="Iniciar patrulla {{ $a->id }}">
                          <i class="fas fa-play"></i> Iniciar
                        </button>
                      </form>
                    @endif

                    @if (in_array($a->status, ['scheduled', 'in_progress']))
                      <form method="POST" action="{{ route('patrol.finish', $a) }}" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-outline-success" title="Finalizar patrulla #{{ $a->id }}" aria-label="Finalizar patrulla {{ $a->id }}">
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
    </div>

    {{-- ===== Móvil: Cards ===== --}}
    <div class="mobile-cards">
      @forelse ($assignments as $a)
        @php
          $route = $a->route;
          $branchName = $route->branch->name ?? '—';

          $done = (int) ($a->scans_count ?? 0);
          $totalFromSnapshot = (int) ($a->checkpoints_total ?? 0);

          if ($totalFromSnapshot === 0) {
            if ($a->status === 'completed') {
              $total = max(1, $done);
            } else {
              $total = (int) ($route?->checkpoints?->count() ?? 0);
            }
          } else {
            $total = $totalFromSnapshot;
          }

          $progress = $total ? intval(min(100, ($done * 100) / $total)) : 0;

          $clr = [
            'scheduled'   => 'secondary',
            'in_progress' => 'info',
            'completed'   => 'success',
            'missed'      => 'warning',
            'cancelled'   => 'dark',
          ][$a->status] ?? 'secondary';

          $estadoEspañol = $estadoTranslation[$a->status] ?? 'Desconocido';

          $canScan = in_array($a->status, ['scheduled', 'in_progress']) && ($total === 0 || $done < $total);
        @endphp

        <div class="vg-card">
          <div class="vg-header">
            <div class="fw-semibold">{{ $route->name ?? '—' }}</div>
            <span class="badge bg-{{ $clr }} badge-status">{{ $estadoEspañol }}</span>
          </div>
          <div class="vg-sub">#{{ $a->id }} • {{ $branchName }}</div>

          <div class="vg-body">
            <div class="vg-row">
              <div class="vg-label">Inicio</div>
              <div class="vg-value">{{ $a->scheduled_start }}</div>
            </div>
            <div class="vg-row">
              <div class="vg-label">Fin</div>
              <div class="vg-value">{{ $a->scheduled_end }}</div>
            </div>
            <div class="vg-row" aria-label="Progreso de patrulla">
              <div class="vg-label">Progreso</div>
              <div class="vg-value">{{ $done }}/{{ $total }} ({{ $progress }}%)</div>
            </div>
            <div class="progress progress-sm mt-2" role="progressbar" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
              <div class="progress-bar" style="width: {{ $progress }}%;"></div>
            </div>
          </div>

          <div class="vg-actions">
            @if ($canScan)
              <a href="{{ route('patrol.scan') }}?a={{ $a->id }}"
                 class="btn btn-outline-primary btn-sm"
                 title="Escanear patrulla #{{ $a->id }}" aria-label="Escanear patrulla {{ $a->id }}">
                <i class="fas fa-qrcode"></i> Escanear
              </a>
            @else
              <button class="btn btn-outline-secondary btn-sm" disabled title="Escaneo deshabilitado">
                <i class="fas fa-qrcode"></i> Escaneado
              </button>
            @endif

            @if ($a->status === 'scheduled')
              <form method="POST" action="{{ route('patrol.start', $a) }}">
                @csrf
                <button class="btn btn-sm btn-success" title="Iniciar patrulla #{{ $a->id }}" aria-label="Iniciar patrulla {{ $a->id }}">
                  <i class="fas fa-play"></i> Iniciar
                </button>
              </form>
            @endif

            @if (in_array($a->status, ['scheduled', 'in_progress']))
              <form method="POST" action="{{ route('patrol.finish', $a) }}">
                @csrf
                <button class="btn btn-sm btn-outline-success" title="Finalizar patrulla #{{ $a->id }}" aria-label="Finalizar patrulla {{ $a->id }}">
                  <i class="fas fa-flag-checkered"></i> Finalizar
                </button>
              </form>
            @endif
          </div>
        </div>
      @empty
        <div class="text-center text-muted py-3">No tenés asignaciones.</div>
      @endforelse
    </div>

    @if (method_exists($assignments, 'links'))
      <div class="mt-2">{{ $assignments->links() }}</div>
    @endif
  </x-adminlte-card>
@endsection
