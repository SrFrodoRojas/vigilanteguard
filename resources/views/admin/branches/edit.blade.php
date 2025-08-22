@extends('adminlte::page')

@section('title', 'Editar Sucursal')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap">
        <div>
            <h1 class="mb-0">Editar Sucursal: {{ $branch->name }}</h1>
            <small class="text-muted">Actualiza datos, color de identificación y encargado</small>
        </div>
        <div class="mt-2 mt-md-0">
            <a href="{{ route('branches.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@endsection

@section('content')
    @if (session('success'))
        <x-adminlte-alert theme="success" class="mb-3">
            <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
        </x-adminlte-alert>
    @endif

    @if ($errors->any())
        <x-adminlte-alert theme="danger" class="mb-3" title="Revisa los datos">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    {{-- ==================== FORMULARIO PRINCIPAL ==================== --}}
    <x-adminlte-card theme="primary" icon="fas fa-building" title="Datos de la Sucursal">
        <form method="POST" action="{{ route('branches.update', $branch) }}" autocomplete="off">
            @csrf
            @method('PUT')

            @php
                // Color por defecto si no hay en BD
                $hue     = ($branch->id * 47) % 360;
                $defTone = "hsl($hue, 70%, 45%)";
                $hex     = $branch->color ?? null; // si ya migraste 'color' en branches
                // Si viniera en HSL u otro formato, mostramos el valor tal cual; el input color requiere HEX
                $hexForPicker = preg_match('/^#([0-9a-f]{3}){1,2}$/i', (string) $hex) ? $hex : '#4472c4';
                $mgr          = $branch->manager;
                $phone        = $mgr?->phone;
                $digits       = $phone ? preg_replace('/\D+/', '', $phone) : null;
            @endphp

            <div class="row">
                <div class="col-md-5">
                    <x-adminlte-input name="name" label="Nombre" placeholder="Nombre de la sucursal"
                        fgroup-class="col-md-12" value="{{ old('name', $branch->name) }}" required/>
                </div>
                <div class="col-md-5">
                    <x-adminlte-input name="location" label="Ubicación" placeholder="Ciudad, zona, referencia"
                        fgroup-class="col-md-12" value="{{ old('location', $branch->location) }}" required/>
                </div>

                {{-- Color de sucursal (HEX) --}}
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="color">Color (opcional)</label>
                        <div class="d-flex align-items-center">
                            <input type="color" id="color_picker"
                                   class="form-control mr-2"
                                   style="width: 58px; padding: 0;"
                                   value="{{ old('color', $hexForPicker) }}">
                            <input type="text" id="color" name="color"
                                   class="form-control"
                                   placeholder="#RRGGBB"
                                   value="{{ old('color', $hex ?? $hexForPicker) }}">
                        </div>
                        <small class="text-muted">Se usa para resaltar esta sede en las listas.</small>
                    </div>
                </div>
            </div>

            {{-- Encargado --}}
            <div class="row">
                <div class="col-md-6">
                    <x-adminlte-select2 name="manager_id" label="Encargado (admin)"
                                        fgroup-class="col-md-12">
                        <option value="">— Sin encargado —</option>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}"
                                {{ (string) old('manager_id', $branch->manager_id) === (string) $manager->id ? 'selected' : '' }}>
                                {{ $manager->name }} ({{ $manager->email }})
                            </option>
                        @endforeach
                    </x-adminlte-select2>
                    @if($mgr)
                        <div class="small mt-1">
                            <i class="fas fa-envelope text-muted"></i> {{ $mgr->email }}
                            @if($phone)
                                <span class="ml-2">
                                    <a href="tel:{{ $digits ?: $phone }}" class="mr-2">
                                        <i class="fas fa-phone"></i> {{ $phone }}
                                    </a>
                                    @if($digits)
                                        <a href="https://wa.me/{{ $digits }}" target="_blank" class="text-success">
                                            <i class="fab fa-whatsapp"></i> WhatsApp
                                        </a>
                                    @endif
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="d-flex justify-content-between mt-3">
                <a href="{{ route('branches.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>

                <div class="d-flex align-items-center">
                    {{-- Botón Acciones masivas (solo si existe la ruta) --}}
                    @if (\Illuminate\Support\Facades\Route::has('branches.mass-update'))
                        <button type="button" class="btn btn-outline-dark mr-2" data-toggle="modal" data-target="#massActionsModal">
                            <i class="fas fa-tools"></i> Acciones masivas
                        </button>
                    @endif

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar
                    </button>
                </div>
            </div>
        </form>
    </x-adminlte-card>

    {{-- ==================== GUARDIAS ASIGNADOS ==================== --}}
    <x-adminlte-card theme="info" icon="fas fa-users" title="Guardias asignados" collapsible="true" removable="true">
        @if($branch->users->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm align-middle">
                    <thead class="thead-light">
                        <tr>
                            <th style="min-width: 180px;">Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($branch->users as $user)
                            @php
                                $phoneU  = $user->phone;
                                $digitsU = $phoneU ? preg_replace('/\D+/', '', $phoneU) : null;
                            @endphp
                            <tr>
                                <td class="align-middle">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle mr-2" title="{{ $user->name }}">
                                            {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                        </div>
                                        <span>{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td class="align-middle">{{ $user->email }}</td>
                                <td class="align-middle text-nowrap">
                                    @if($phoneU)
                                        <a href="tel:{{ $digitsU ?: $phoneU }}" class="mr-2">
                                            <i class="fas fa-phone"></i> {{ $phoneU }}
                                        </a>
                                        @if($digitsU)
                                            <a href="https://wa.me/{{ $digitsU }}" target="_blank" class="text-success" title="WhatsApp">
                                                <i class="fab fa-whatsapp"></i>
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    @if($user->is_active)
                                        <span class="badge badge-success"><i class="fas fa-check mr-1"></i> Activo</span>
                                    @else
                                        <span class="badge badge-danger"><i class="fas fa-times mr-1"></i> Inactivo</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-adminlte-alert theme="warning" icon="fas fa-info-circle">
                No hay guardias asignados a esta sucursal.
            </x-adminlte-alert>
        @endif
    </x-adminlte-card>

    {{-- ==================== MODAL ACCIONES MASIVAS (OPCIONAL) ==================== --}}
    @if (\Illuminate\Support\Facades\Route::has('branches.mass-update'))
        <div class="modal fade" id="massActionsModal" tabindex="-1" role="dialog" aria-labelledby="massActionsLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable" role="document">
                <form method="POST" action="{{ route('branches.mass-update', $branch) }}" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="massActionsLabel">
                            <i class="fas fa-tools mr-1"></i> Acciones masivas sobre guardias de <strong>{{ $branch->name }}</strong>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <p class="text-muted">Estas acciones se aplicarán a <strong>todos los guardias</strong> de esta sucursal.</p>

                        <div class="form-group">
                            <label>¿Qué querés hacer?</label>
                            <select name="action" class="form-control" required>
                                <option value="">— Seleccionar —</option>
                                <option value="activate_all">Activar a todos</option>
                                <option value="deactivate_all">Desactivar a todos</option>
                                <option value="unassign_all">Quitar sucursal (dejar sin asignación)</option>
                                <option value="transfer_all">Transferir a otra sucursal…</option>
                            </select>
                        </div>

                        <div class="form-group" id="targetBranchWrap" style="display:none;">
                            <label>Transferir a:</label>
                            <select name="target_branch_id" class="form-control">
                                <option value="">— Seleccionar sucursal —</option>
                                @foreach(\App\Models\Branch::orderBy('name')->get() as $b)
                                    @if($b->id !== $branch->id)
                                        <option value="{{ $b->id }}">{{ $b->name }} — {{ $b->location }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <small class="text-muted">Moverá todos los guardias de {{ $branch->name }} a la sucursal seleccionada.</small>
                        </div>

                        <div class="form-group mb-0">
                            <label>Confirmación</label>
                            <input type="text" class="form-control" name="confirm_text" placeholder="Escribe: CONFIRMAR" required>
                            <small class="text-muted">Para continuar, escribí exactamente <code>CONFIRMAR</code>.</small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-dark">
                            <i class="fas fa-check"></i> Aplicar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('css')
<style>
    .avatar-circle {
        width: 28px; height: 28px; border-radius: 50%;
        background: #e9ecef; color: #495057;
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: .85rem;
    }
    @media (max-width: 767.98px) {
        .btn { padding: .35rem .6rem; font-size: .9rem; }
        .form-control, .form-control-sm { font-size: .9rem; }
        .card-body { padding: .9rem; }
        .table { font-size: .9rem; }
    }
</style>
@endpush

@push('js')
<script>
    // Sincroniza picker <-> hex
    (function(){
        const picker = document.getElementById('color_picker');
        const input  = document.getElementById('color');
        if(!picker || !input) return;

        function toHex(v){
            // si ya es hex válido lo devolvemos
            if(/^#([0-9a-f]{3}){1,2}$/i.test(v)) return v;
            // caso contrario no transformamos (evita romper HSL)
            return v;
        }

        picker.addEventListener('input', () => {
            input.value = picker.value;
        });

        input.addEventListener('blur', () => {
            const v = input.value.trim();
            if(v && /^#([0-9a-f]{3}){1,2}$/i.test(v)){
                picker.value = v;
            }
        });
    })();

    // Mostrar combo "transferir a" sólo si corresponde
    (function(){
        const actionSel = document.querySelector('[name="action"]');
        const wrap      = document.getElementById('targetBranchWrap');
        if(!actionSel || !wrap) return;
        function sync(){ wrap.style.display = (actionSel.value === 'transfer_all') ? '' : 'none'; }
        actionSel.addEventListener('change', sync);
        sync();
    })();
</script>
@endpush
