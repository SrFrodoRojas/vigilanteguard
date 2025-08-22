{{-- resources/views/admin/patrol/routes/create.blade.php --}}
@extends('adminlte::page')
@section('title', 'Nueva Ruta')

@section('content_header')
    <h1>
        Nueva Ruta</h1>
@endsection

@section('content')

    @if ($errors->any())
        <x-adminlte-alert theme="danger" title="Error">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    <form method="POST" action="{{ route('admin.patrol.routes.store') }}">
        @csrf

        <div class="row">
            <div class="col-md-6">
                <x-adminlte-select name="branch_id" label="Sucursal" required>
                    @foreach ($branches as $id => $name)
                        <option value="{{ $id }}" @selected(old('branch_id') == $id)>{{ $name }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>
            <div class="col-md-6">
                <x-adminlte-input name="name" label="Nombre" value="{{ old('name') }}" required />
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <x-adminlte-input type="number" name="expected_duration_min" label="Duración esperada (min)"
                    value="{{ old('expected_duration_min', 30) }}" min="5" max="480" required />
            </div>
            <div class="col-md-4">
                {{-- Activa --}}
                <div class="form-group">
                    <label class="form-label d-block mb-1">Activa</label>
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" name="active" data-toggle="toggle" :checked="(bool) old('active', true)" />
                </div>
            </div>

            <div class="col-md-4">
                {{-- QR obligatorio --}}
                <div class="form-group">
                    <label class="form-label d-block mb-1">QR obligatorio</label>
                    <input type="hidden" name="qr_required" value="0">
                    <input type="checkbox" name="qr_required" data-toggle="toggle"
                        :checked="(bool) old('qr_required', true)" />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <x-adminlte-input type="number" name="min_radius_m" label="Radio mínimo GPS (m)"
                    value="{{ old('min_radius_m', 20) }}" min="5" max="200" />
            </div>
        </div>

        <x-adminlte-button type="submit" class="mt-2" label="Guardar" theme="primary" icon="fas fa-save" />
        <a href="{{ route('admin.patrol.routes.index') }}" class="btn btn-outline-secondary mt-2">Cancelar</a>
    </form>
@endsection
