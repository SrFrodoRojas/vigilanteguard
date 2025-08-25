{{-- resources/views/admin/patrol/routes/index.blade.php --}}
@extends('adminlte::page')
@section('title', 'Rutas de Patrulla')

@push('css')
<style>
  /* Cards solo en móviles */
  @media (max-width: 767.98px){
    .route-card .kv { display: grid; grid-template-columns: 120px 1fr; gap: .25rem .5rem; }
    .route-card .actions > * { width: 100%; }
  }
</style>
@endpush

@section('content_header')
  <h1>Rutas de Patrulla</h1>
@endsection

@section('content')

  {{-- FLASH / VALIDACIÓN (mejorados) --}}
  @php
    $flashMap = [
      'success' => ['theme'=>'success', 'title'=>'OK'],
      'warning' => ['theme'=>'warning', 'title'=>'Atención'],
      'info'    => ['theme'=>'info',    'title'=>'Info'],
      'error'   => ['theme'=>'danger',  'title'=>'Error'],
      'status'  => ['theme'=>'info',    'title'=>'Estado'],
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

  <x-adminlte-card theme="light" title="Listado de rutas" icon="fas fa-route">
    {{-- CTA --}}
    <div class="mb-3 d-grid d-md-block">
      <a href="{{ route('admin.patrol.routes.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nueva ruta
      </a>
    </div>

    {{-- DESKTOP: Tabla (>= md) --}}
    <div class="table-responsive d-none d-md-block">
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
              if (Route::has('admin.patrol.routes.checkpoints.index')) {
                  $checkpointsUrl = route('admin.patrol.routes.checkpoints.index', $r);
              } elseif (Route::has('admin.patrol.checkpoints.index')) {
                  $checkpointsUrl = route('admin.patrol.checkpoints.index', ['route' => $r->id]);
              } else {
                  $checkpointsUrl = url('/admin/patrol/routes/'.$r->id.'/checkpoints');
              }
            @endphp
            <tr>
              <td>{{ $r->id }}</td>
              <td class="fw-semibold">{{ $r->name }}</td>
              <td>{{ $r->branch->name ?? '—' }}</td>
              <td>{{ $r->expected_duration_min }} min</td>
              <td>{{ $r->min_radius_m }} m</td>
              <td>
                <span class="badge bg-{{ $r->qr_required ? 'info' : 'dark' }}">{{ $r->qr_required ? 'Sí' : 'No' }}</span>
              </td>
              <td>
                <span class="badge bg-{{ $badge }}">{{ $label }}</span>
              </td>
              <td class="text-end">
                <div class="d-flex flex-wrap justify-content-end gap-2">
                  <a class="btn btn-outline-secondary btn-sm" href="{{ $checkpointsUrl }}">
                    <i class="fas fa-map-marker-alt"></i> Checkpoints
                  </a>
                  <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.patrol.routes.edit', $r) }}">
                    <i class="fas fa-edit"></i> Editar
                  </a>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center text-muted">No hay rutas.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- MÓVIL: Cards (< md) --}}
    <div class="d-md-none">
      @forelse ($routes as $r)
        @php
          $badge = $r->active ? 'success' : 'secondary';
          $label = $r->active ? 'Activo' : 'Inactivo';
          if (Route::has('admin.patrol.routes.checkpoints.index')) {
              $checkpointsUrl = route('admin.patrol.routes.checkpoints.index', $r);
          } elseif (Route::has('admin.patrol.checkpoints.index')) {
              $checkpointsUrl = route('admin.patrol.checkpoints.index', ['route' => $r->id]);
          } else {
              $checkpointsUrl = url('/admin/patrol/routes/'.$r->id.'/checkpoints');
          }
        @endphp

        <div class="card mb-3 route-card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <div class="small text-muted">#{{ $r->id }}</div>
                <h5 class="card-title mb-0">{{ $r->name }}</h5>
              </div>
              <span class="badge bg-{{ $badge }} ms-2">{{ $label }}</span>
            </div>

            <div class="kv mb-3">
              <div class="text-muted">Sucursal</div>
              <div>{{ $r->branch->name ?? '—' }}</div>

              <div class="text-muted">Duración</div>
              <div>{{ $r->expected_duration_min }} min</div>

              <div class="text-muted">Radio mínimo</div>
              <div>{{ $r->min_radius_m }} m</div>

              <div class="text-muted">QR requerido</div>
              <div>
                <span class="badge bg-{{ $r->qr_required ? 'info' : 'dark' }}">{{ $r->qr_required ? 'Sí' : 'No' }}</span>
              </div>
            </div>

            <div class="d-flex flex-column gap-2 actions">
              <a class="btn btn-outline-secondary" href="{{ $checkpointsUrl }}">
                <i class="fas fa-map-marker-alt"></i> Checkpoints
              </a>
              <a class="btn btn-outline-primary" href="{{ route('admin.patrol.routes.edit', $r) }}">
                <i class="fas fa-edit"></i> Editar
              </a>
            </div>
          </div>
        </div>
      @empty
        <div class="text-center text-muted py-3">No hay rutas.</div>
      @endforelse
    </div>

    @if (method_exists($routes, 'links'))
      <div class="mt-2">{{ $routes->links() }}</div>
    @endif
  </x-adminlte-card>
@endsection
