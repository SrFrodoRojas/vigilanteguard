@extends('adminlte::page')
@section('title', 'Registrar entrada')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap">
        <div>
            <h1 class="mb-0">Registrar entrada</h1>
            <small class="text-muted">Vehículo o A pie · con sucursal</small>
        </div>
        <div class="mt-2 mt-md-0 d-flex flex-wrap">
            <a href="{{ route('access.index') }}" class="btn btn-secondary btn-sm mr-2 mb-2">
                <i class="fas fa-list"></i> Ver listados
            </a>
            <a href="{{ route('access.active') }}" class="btn btn-outline-warning btn-sm mr-2 mb-2">
                <i class="fas fa-user-check"></i> Ver Activos
            </a>
            <a href="{{ route('access.exit.form') }}" class="btn btn-outline-primary btn-sm mb-2">
                <i class="fas fa-sign-out-alt"></i> Registrar salida
            </a>
        </div>
    </div>
@endsection

@section('content')
    @if ($errors->any())
        <x-adminlte-alert theme="danger" title="Revisa los datos">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    <x-adminlte-card theme="primary" icon="fas fa-door-open" title="Datos de acceso" class="mb-3">
        <form method="POST" action="{{ route('access.store') }}" id="access-form" autocomplete="off">
            @csrf

            {{-- Sucursal (solo admin puede elegir) --}}
            @if(!empty($isAdmin) && $isAdmin)
                <div class="form-group">
                    <label>Sucursal</label>
                    <select name="branch_id" class="form-control form-control-sm @error('branch_id') is-invalid @enderror">
                        <option value="">— Seleccionar sucursal —</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ (string)old('branch_id') === (string)$b->id ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('branch_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            @else
                {{-- No-admin: sucursal fija (informativo + hidden) --}}
                <div class="form-group">
                    <label>Sucursal</label><br>
                    <span class="badge badge-info">{{ auth()->user()->branch->name ?? '—' }}</span>
                </div>
            @endif

            {{-- Tipo de acceso --}}
            <div class="form-group">
                <label class="d-block">Tipo de acceso</label>
                @php $typeOld = old('type','vehicle'); @endphp
                <div class="custom-control custom-radio custom-control-inline">
                    <input class="custom-control-input" type="radio" id="type_vehicle" name="type" value="vehicle"
                        {{ $typeOld === 'vehicle' ? 'checked' : '' }}>
                    <label class="custom-control-label" for="type_vehicle">Vehículo</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    <input class="custom-control-input" type="radio" id="type_pedestrian" name="type"
                        value="pedestrian" {{ $typeOld === 'pedestrian' ? 'checked' : '' }}>
                    <label class="custom-control-label" for="type_pedestrian">A pie</label>
                </div>
            </div>

            {{-- VEHÍCULO --}}
            <div id="vehicle-fields" style="{{ $typeOld === 'vehicle' ? '' : 'display:none;' }}">
                <div class="form-row">
                    <div class="form-group col-12 col-md-4">
                        <label>Placa <small class="text-muted">(ABC-123)</small></label>
                        <input name="plate" id="plate" class="form-control form-control-sm @error('plate') is-invalid @enderror"
                            value="{{ old('plate') }}" placeholder="ABC-123" maxlength="10" pattern="[A-Za-z0-9\-]{3,10}"
                            style="text-transform:uppercase">
                        @error('plate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group col-12 col-md-4">
                        <label>Marca (opcional)</label>
                        <input name="marca_vehiculo" class="form-control form-control-sm" value="{{ old('marca_vehiculo') }}"
                            placeholder="Toyota / Honda / ...">
                    </div>
                    <div class="form-group col-12 col-md-4">
                        <label>Color (opcional)</label>
                        <input name="color_vehiculo" class="form-control form-control-sm" value="{{ old('color_vehiculo') }}"
                            placeholder="Rojo / Negro / ...">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-12 col-md-4">
                        <label>Tipo (opcional)</label>
                        <select name="tipo_vehiculo" class="form-control form-control-sm">
                            <option value="">— Seleccionar —</option>
                            @foreach (['auto', 'moto', 'bicicleta', 'camion'] as $op)
                                <option value="{{ $op }}" {{ old('tipo_vehiculo') === $op ? 'selected' : '' }}>
                                    {{ ucfirst($op) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr class="my-3">
                <h5>Chofer</h5>
                <div class="form-row">
                    <div class="form-group col-12 col-md-4">
                        <label>Documento</label>
                        <input name="document" class="form-control form-control-sm @error('document') is-invalid @enderror"
                            value="{{ old('document') }}" placeholder="1234567">
                        @error('document') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group col-12 col-md-5">
                        <label>Nombre y apellido</label>
                        <input name="full_name" class="form-control form-control-sm @error('full_name') is-invalid @enderror"
                            value="{{ old('full_name') }}" placeholder="Juan Pérez">
                        @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group col-12 col-md-3">
                        <label>Sexo/Género (opcional)</label>
                        <select name="gender" class="form-control form-control-sm">
                            <option value="">— Seleccionar —</option>
                            @foreach (['femenino', 'masculino'] as $op)
                                <option value="{{ $op }}" {{ old('gender') === $op ? 'selected' : '' }}>
                                    {{ ucfirst($op) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr class="my-3">
                <div class="d-flex align-items-center mb-2">
                    <h5 class="mb-0">Acompañantes (opcional)</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary ml-3" onclick="addPassenger()">
                        <i class="fas fa-user-plus"></i> Agregar acompañante
                    </button>
                </div>
                <div id="passengers-wrapper">
                    @php $oldPassengers = old('passengers', []); @endphp
                    @foreach ($oldPassengers as $idx => $p)
                        <div class="border rounded p-2 mb-2 passenger-block">
                            <div class="form-row">
                                <div class="form-group col-12 col-md-4">
                                    <label>Documento</label>
                                    <input name="passengers[{{ $idx }}][document]" class="form-control form-control-sm"
                                        value="{{ $p['document'] ?? '' }}" placeholder="Documento">
                                </div>
                                <div class="form-group col-12 col-md-5">
                                    <label>Nombre y apellido</label>
                                    <input name="passengers[{{ $idx }}][full_name]" class="form-control form-control-sm"
                                        value="{{ $p['full_name'] ?? '' }}" placeholder="Nombre y apellido">
                                </div>
                                <div class="form-group col-12 col-md-3">
                                    <label>Sexo/Género (opcional)</label>
                                    <select name="passengers[{{ $idx }}][gender]" class="form-control form-control-sm">
                                        <option value="">— Seleccionar —</option>
                                        @foreach (['femenino', 'masculino'] as $op)
                                            <option value="{{ $op }}" {{ ($p['gender'] ?? '') === $op ? 'selected' : '' }}>
                                                {{ ucfirst($op) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <button type="button" class="btn btn-xs btn-outline-danger"
                                onclick="this.closest('.passenger-block').remove()">
                                <i class="fas fa-times"></i> Quitar
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- A PIE --}}
            <div id="pedestrian-fields" style="{{ $typeOld === 'pedestrian' ? '' : 'display:none;' }}">
                <div class="form-row">
                    <div class="form-group col-12 col-md-4">
                        <label>Documento</label>
                        <input name="document" class="form-control form-control-sm @error('document') is-invalid @enderror"
                            value="{{ old('document') }}" placeholder="1234567">
                        @error('document') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group col-12 col-md-5">
                        <label>Nombre y apellido</label>
                        <input name="full_name" class="form-control form-control-sm @error('full_name') is-invalid @enderror"
                            value="{{ old('full_name') }}" placeholder="Juan Pérez">
                        @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group col-12 col-md-3">
                        <label>Sexo/Género (opcional)</label>
                        <select name="gender" class="form-control form-control-sm">
                            <option value="">— Seleccionar —</option>
                            @foreach (['femenino', 'masculino'] as $op)
                                <option value="{{ $op }}" {{ old('gender') === $op ? 'selected' : '' }}>
                                    {{ ucfirst($op) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Observación de entrada --}}
            <div class="form-group">
                <label>Observación (opcional)</label>
                <textarea name="entry_note" class="form-control form-control-sm" rows="2"
                    placeholder="Ej.: Conductor de plataforma, visita, proveedor, X motivo de ingreso, etc.">{{ old('entry_note') }}</textarea>
            </div>

            <div class="mt-3 d-flex flex-wrap gap-2">
                <button class="btn btn-primary btn-sm">
                    <i class="fas fa-check"></i> Guardar entrada
                </button>
                <a href="{{ route('access.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
            </div>
        </form>
    </x-adminlte-card>
@endsection

@push('css')
<style>
    /* Compactar en móvil */
    @media (max-width: 767.98px) {
        .btn { padding: .35rem .6rem; font-size: .9rem; }
        .form-control, .form-control-sm, .input-group-text { font-size: .9rem; }
        .card-body { padding: .9rem; }
        label { margin-bottom: .2rem; }
    }
</style>
@endpush

@push('js')
<script>
    // Helpers
    function setDisabled(container, disabled) {
        container.querySelectorAll('input, select, textarea').forEach(el => el.disabled = !!disabled);
    }
    function sanitizePlate(val) {
        return (val || '').toUpperCase().replace(/\s+/g, '');
    }
    function toggleTypeUI() {
        const isVehicle = document.getElementById('type_vehicle').checked;
        const vBlock = document.getElementById('vehicle-fields');
        const pBlock = document.getElementById('pedestrian-fields');

        vBlock.style.display = isVehicle ? '' : 'none';
        pBlock.style.display = isVehicle ? 'none' : '';

        setDisabled(vBlock, !isVehicle);
        setDisabled(pBlock, isVehicle);

        // UX: required dinámico de placa
        const plateInput = document.getElementById('plate');
        if (plateInput) plateInput.required = isVehicle;

        // Re-vincular lookup principal al bloque visible
        bindMainLookup();
    }

    // Uppercase + sin espacios para placa
    const plate = document.getElementById('plate');
    if (plate) plate.addEventListener('input', () => plate.value = sanitizePlate(plate.value));

    // Acompañantes dinámicos
    let pIndex = {{ is_array(old('passengers')) ? count(old('passengers')) : 0 }};
    function addPassenger() {
        const wrap = document.getElementById('passengers-wrapper');
        const html = `
<div class="border rounded p-2 mb-2 passenger-block">
  <div class="form-row">
    <div class="form-group col-12 col-md-4">
      <label>Documento</label>
      <input name="passengers[${pIndex}][document]" class="form-control form-control-sm" placeholder="Documento">
    </div>
    <div class="form-group col-12 col-md-5">
      <label>Nombre y apellido</label>
      <input name="passengers[${pIndex}][full_name]" class="form-control form-control-sm" placeholder="Nombre y apellido">
    </div>
    <div class="form-group col-12 col-md-3">
      <label>Sexo/Género (opcional)</label>
      <select name="passengers[${pIndex}][gender]" class="form-control form-control-sm">
        <option value="">— Seleccionar —</option>
        <option value="femenino">Femenino</option>
        <option value="masculino">Masculino</option>
      </select>
    </div>
  </div>
  <button type="button" class="btn btn-xs btn-outline-danger" onclick="this.closest('.passenger-block').remove()">
    <i class="fas fa-times"></i> Quitar
  </button>
</div>`;
        wrap.insertAdjacentHTML('beforeend', html);
        pIndex++;
    }

    // Lookup maestro people
    async function fetchPerson(doc) {
        if (!doc) return null;
        const url = `{{ route('people.lookup') }}?document=${encodeURIComponent(doc)}`;
        try {
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return null;
            return await res.json();
        } catch { return null; }
    }

    // Vincular lookup principal al bloque visible (vehículo o peatón)
    function bindMainLookup() {
        // Limpia listeners previos clonando nodos
        document.querySelectorAll('#vehicle-fields input[name="document"], #pedestrian-fields input[name="document"]').forEach(el => {
            const clone = el.cloneNode(true);
            el.parentNode.replaceChild(clone, el);
        });

        const container = document.getElementById(document.getElementById('type_vehicle').checked ? 'vehicle-fields' : 'pedestrian-fields');
        const docMain = container.querySelector('input[name="document"]:not([disabled])');
        const nameMain = container.querySelector('input[name="full_name"]:not([disabled])');
        const genderMain = container.querySelector('select[name="gender"]:not([disabled])');

        if (docMain && nameMain) {
            docMain.addEventListener('blur', async () => {
                const val = docMain.value.trim();
                if (!val) return;
                const j = await fetchPerson(val);
                if (j && j.found) {
                    if (!nameMain.value?.trim()) nameMain.value = j.full_name || '';
                    if (genderMain && !genderMain.value && (j.gender === 'femenino' || j.gender === 'masculino')) {
                        genderMain.value = j.gender;
                    }
                }
            });
        }
    }

    // Delegado para acompañantes
    document.getElementById('passengers-wrapper').addEventListener('blur', async (e) => {
        const el = e.target;
        if (!el.name || !el.name.includes('[document]')) return;
        const doc = el.value.trim();
        if (!doc) return;
        const block = el.closest('.passenger-block');
        const nameInput = block?.querySelector('input[name^="passengers"][name$="[full_name]"]');
        const genderSelect = block?.querySelector('select[name^="passengers"][name$="[gender]"]');
        const j = await fetchPerson(doc);
        if (j && j.found) {
            if (nameInput && !nameInput.value?.trim()) nameInput.value = j.full_name || '';
            if (genderSelect && !genderSelect.value && (j.gender === 'femenino' || j.gender === 'masculino')) {
                genderSelect.value = j.gender;
            }
        }
    }, true);

    // Eventos tipo y init
    document.getElementById('type_vehicle').addEventListener('change', toggleTypeUI);
    document.getElementById('type_pedestrian').addEventListener('change', toggleTypeUI);
    toggleTypeUI(); // estado inicial
</script>
@endpush
