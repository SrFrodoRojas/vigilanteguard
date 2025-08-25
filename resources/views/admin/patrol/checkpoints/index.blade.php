{{-- resources/views/admin/patrol/checkpoints/index.blade.php --}}
@extends('adminlte::page')
@section('title','Checkpoints')

@section('content_header')
  <h1>Checkpoints • {{ $route->name }}</h1>
@endsection

@section('content')

  {{-- FLASH / VALIDACIÓN mejorados --}}
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
      <ul class="mb-0">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
    </x-adminlte-alert>
  @endif

  {{-- CTA --}}
  <div class="mb-3 d-grid d-md-inline-block">
    <a href="{{ route('admin.patrol.routes.checkpoints.create',$route) }}" class="btn btn-primary">
      <i class="fas fa-plus"></i> Nuevo checkpoint
    </a>
  </div>

  <x-adminlte-card>

    {{-- DESKTOP: Tabla (>= md) --}}
    <div class="table-responsive d-none d-md-block">
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Lat</th>
            <th>Lng</th>
            <th>Radio</th>
            <th>QR</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($checkpoints as $cp)
            @php
              $qrUrl = route('admin.patrol.checkpoints.qr', $cp);
            @endphp
            <tr>
              <td>{{ $cp->id }}</td>
              <td class="fw-semibold">{{ $cp->name }}</td>
              <td>{{ $cp->latitude }}</td>
              <td>{{ $cp->longitude }}</td>
              <td>{{ $cp->radius_m }} m</td>
              <td>
                <a href="{{ $qrUrl }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                  <i class="fas fa-qrcode"></i> Ver QR
                </a>
              </td>
              <td class="text-end">
                <div class="d-flex flex-wrap justify-content-end gap-2">
                  <a class="btn btn-sm btn-outline-primary"
                     href="{{ route('admin.patrol.checkpoints.edit',$cp) }}">
                    <i class="fas fa-edit"></i> Editar
                  </a>
                  <form action="{{ route('admin.patrol.checkpoints.destroy',$cp) }}" method="POST"
                        class="d-inline"
                        onsubmit="return confirm('¿Eliminar checkpoint #{{ $cp->id }}?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">
                      <i class="fas fa-trash-alt"></i> Eliminar
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted">No hay checkpoints.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- MÓVIL: Cards (< md) --}}
    <div class="d-md-none">
      @forelse($checkpoints as $cp)
        @php
          $qrUrl = route('admin.patrol.checkpoints.qr', $cp);
        @endphp
        <div class="card mb-3 cp-card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <div class="small text-muted">#{{ $cp->id }}</div>
                <h5 class="card-title mb-0">{{ $cp->name }}</h5>
              </div>
              <span class="badge bg-info">{{ $cp->radius_m }} m</span>
            </div>

            <div class="kv mb-3">
              <div class="text-muted">Lat</div>
              <div>{{ $cp->latitude }}</div>

              <div class="text-muted">Lng</div>
              <div>{{ $cp->longitude }}</div>

              <div class="text-muted">QR</div>
              <div>
                <a href="{{ $qrUrl }}" target="_blank" class="btn btn-outline-secondary btn-sm w-100">
                  <i class="fas fa-qrcode"></i> Ver QR
                </a>
              </div>
            </div>

            <div class="d-flex gap-2 actions flex-column flex-sm-row">
              <a class="btn btn-outline-primary text-center"
                 href="{{ route('admin.patrol.checkpoints.edit',$cp) }}">
                <i class="fas fa-edit"></i> Editar
              </a>
              <form action="{{ route('admin.patrol.checkpoints.destroy',$cp) }}" method="POST"
                    class="d-flex"
                    onsubmit="return confirm('¿Eliminar checkpoint #{{ $cp->id }}?')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger w-100">
                  <i class="fas fa-trash-alt"></i> Eliminar
                </button>
              </form>
            </div>
          </div>
        </div>
      @empty
        <div class="text-center text-muted py-3">No hay checkpoints.</div>
      @endforelse
    </div>

    @if(method_exists($checkpoints,'links'))
      <div class="mt-2">{{ $checkpoints->links() }}</div>
    @endif
  </x-adminlte-card>
@endsection

@push('css')
<style>
  /* Cards solo en móviles */
  @media (max-width: 767.98px){
    .cp-card .kv { display: grid; grid-template-columns: 110px 1fr; gap: .25rem .5rem; }
    .cp-card .actions > * { width: 100%; margin-top: 3% }          /* fill button width inside each child */
    .cp-card .actions > * { flex: 1 1 0; }          /* equal widths side by side */
  }
</style>
@endpush

