@extends('adminlte::page')

@section('title', 'Crear Sucursal')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap">
        <div>
            <h1 class="mb-0">Crear Sucursal</h1>
            <small class="text-muted">Define nombre, ubicación, encargado y un color distintivo</small>
        </div>
        <a href="{{ route('branches.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
    @if ($errors->any())
        <x-adminlte-alert theme="danger" title="Revisa los datos" class="mb-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    <x-adminlte-card theme="primary" icon="fas fa-building" title="Datos de la Sucursal">
        <form method="POST" action="{{ route('branches.store') }}" autocomplete="off">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-input
                        name="name"
                        label="Nombre"
                        placeholder="Ej.: Casa Central"
                        fgroup-class="col-md-12"
                        value="{{ old('name') }}"
                        required
                    />
                </div>

                <div class="col-md-6">
                    <x-adminlte-input
                        name="location"
                        label="Ubicación"
                        placeholder="Ej.: Av. Principal 123, Asunción"
                        fgroup-class="col-md-12"
                        value="{{ old('location') }}"
                        required
                    />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-select2
                        name="manager_id"
                        label="Encargado (opcional)"
                        fgroup-class="col-md-12"
                        data-placeholder="Seleccione un encargado"
                    >
                        <option value="">Seleccione un encargado</option>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}" {{ old('manager_id') == $manager->id ? 'selected' : '' }}>
                                {{ $manager->name }} ({{ $manager->email }})
                            </option>
                        @endforeach
                    </x-adminlte-select2>
                    <small class="text-muted">Se listan usuarios con rol <strong>admin</strong>.</small>
                </div>

                <div class="col-md-6">
                    <label class="mb-1 d-block">Color de la sucursal (opcional)</label>
                    <div class="d-flex align-items-center">
                        <input
                            type="color"
                            id="branch_color_picker"
                            value="{{ old('color','#6c757d') }}"
                            class="mr-2"
                            style="height: 38px; width: 48px; padding: 0; border: none;"
                        >
                        <input
                            type="text"
                            name="color"
                            id="branch_color"
                            class="form-control @error('color') is-invalid @enderror"
                            placeholder="#6c757d"
                            value="{{ old('color','#6c757d') }}"
                        >
                        @error('color')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <small class="text-muted d-block mt-1">
                        Este color se usará para resaltar filas/badges de esta sucursal.
                    </small>
                    <div class="mt-2">
                        <span id="color_preview" class="badge"
                              style="background-color: {{ old('color','#6c757d') }}; color: #fff;">
                            Vista previa
                        </span>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('branches.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </form>
    </x-adminlte-card>
@stop

@push('js')
<script>
(function () {
    const picker  = document.getElementById('branch_color_picker');
    const input   = document.getElementById('branch_color');
    const preview = document.getElementById('color_preview');

    function normalizeHex(v) {
        if (!v) return '';
        v = v.trim();
        // si viene sin # pero es hex válido, le agregamos
        if (!v.startsWith('#') && /^([0-9a-f]{6}|[0-9a-f]{3})$/i.test(v)) {
            v = '#' + v;
        }
        return v;
    }

    function setColor(val) {
        if (!val) return;
        input.value = val;
        if (preview) preview.style.backgroundColor = val;
    }

    if (picker && input) {
        picker.addEventListener('input', (e) => {
            setColor(e.target.value);
        });

        input.addEventListener('input', (e) => {
            const v = normalizeHex(e.target.value);
            if (preview) preview.style.backgroundColor = v;
            if (/^#([0-9a-f]{6}|[0-9a-f]{3})$/i.test(v)) {
                picker.value = v;
            }
        });
    }
})();
</script>
@endpush
