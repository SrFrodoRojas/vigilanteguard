{{-- resources/views/admin/patrol/routes/create.blade.php --}}
@extends('adminlte::page')
@section('title','Nueva Ruta')

@section('content_header')
  <h1>Nueva Ruta</h1>
@endsection

@section('content')

  @if ($errors->any())
    <x-adminlte-alert theme="danger" title="Error">
      <ul class="mb-0">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
    </x-adminlte-alert>
  @endif

  <x-adminlte-card theme="light" title="Datos de la ruta" icon="fas fa-route">
    <form method="POST" action="{{ route('admin.patrol.routes.store') }}">
      @csrf

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nombre *</label>
          <input type="text" name="name" class="form-control" required
                 value="{{ old('name') }}">
        </div>

        <div class="col-md-6">
          <label class="form-label">Sucursal *</label>
          <select name="branch_id" class="form-select" required>
            <option value="">— Elegí —</option>
            @foreach($branches as $b)
              <option value="{{ $b->id }}" @selected(old('branch_id')==$b->id)>
                {{ $b->name }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">Duración esperada (min) *</label>
          <input type="number" name="expected_duration_min" min="1" step="1"
                 class="form-control" value="{{ old('expected_duration_min', 30) }}" required>
        </div>

        <div class="col-md-4">
          <label class="form-label">Radio mínimo (m) *</label>
          <input type="number" name="min_radius_m" min="1" step="1"
                 class="form-control" value="{{ old('min_radius_m', 20) }}" required>
        </div>

        <div class="col-md-4 d-flex align-items-end">
          <div class="form-check me-4">
            {{-- Hidden para que siempre llegue --}}
            <input type="hidden" name="qr_required" value="0">
            <input class="form-check-input" type="checkbox" id="qr_required" name="qr_required" value="1"
                   @checked(old('qr_required', 1))>
            <label class="form-check-label" for="qr_required">QR requerido</label>
          </div>

          <div class="form-check">
            <input type="hidden" name="active" value="0">
            <input class="form-check-input" type="checkbox" id="active" name="active" value="1"
                   @checked(old('active', 1))>
            <label class="form-check-label" for="active">Activo</label>
          </div>
        </div>
      </div>

      <div class="mt-3">
        <button class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
        <a href="{{ route('admin.patrol.routes.index') }}" class="btn btn-outline-secondary">Volver</a>
      </div>
    </form>
  </x-adminlte-card>
@endsection
