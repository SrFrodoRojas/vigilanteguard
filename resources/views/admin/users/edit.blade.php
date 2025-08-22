@extends('adminlte::page')
@section('title', 'Editar usuario')

@section('content_header')
    <h1>Editar usuario</h1>
@endsection

@section('content')
    @if ($errors->any())
        <x-adminlte-alert theme="danger" title="Revisa los datos">
            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </x-adminlte-alert>
    @endif

    <x-adminlte-card theme="primary" icon="fas fa-user-edit" title="Datos del usuario">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data" autocomplete="off">
            @csrf @method('PUT')
            <input type="hidden" name="is_active" value="0"><!-- asegura boolean -->

            <div class="row">
                <div class="col-md-8">
                    <x-adminlte-input name="name" label="Nombre y apellido" value="{{ old('name', $user->name) }}" required/>
                    <x-adminlte-input name="email" label="Email" type="email" value="{{ old('email', $user->email) }}" required/>

                    <x-adminlte-input name="phone" label="Teléfono (opcional)" value="{{ old('phone', $user->phone) }}"
                                      placeholder="+595 9xx xxx xxx" >
                        @if($user->whatsapp_url)
                            <x-slot name="bottomSlot">
                                <a href="{{ $user->whatsapp_url }}" target="_blank" class="btn btn-sm btn-success mt-1">
                                    <i class="fab fa-whatsapp"></i> Abrir chat
                                </a>
                            </x-slot>
                        @endif
                    </x-adminlte-input>

                    <div class="form-group">
                        <label>Rol</label>
                        <select name="role" class="form-control" required>
                            <option value="">— Seleccionar —</option>
                            @foreach(\Spatie\Permission\Models\Role::whereIn('name',['guardia','admin'])->orderBy('name')->pluck('name','name') as $r)
                                <option value="{{ $r }}" {{ old('role',$currentRole)===$r?'selected':'' }}>{{ $r }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Si el rol es guardia, asignar sucursal.</small>
                    </div>

                    <div class="form-group">
                        <label>Sucursal (solo guardia)</label>
                        <select name="branch_id" class="form-control">
                            <option value="">— Sin sucursal —</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ old('branch_id',$user->branch_id)==$b->id?'selected':'' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="custom-control custom-switch mb-3">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                               {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">Usuario activo</label>
                    </div>

                    <h5 class="mt-3">Seguridad</h5>
                    <x-adminlte-input name="password" label="Nueva contraseña (opcional)" type="password" />
                    <x-adminlte-input name="password_confirmation" label="Confirmar nueva contraseña" type="password" />
                </div>

                <div class="col-md-4">
                    <label>Foto</label>
                    <div class="d-flex align-items-center mb-2">
                        <img id="avatarPreview" class="rounded-circle border" style="width:72px;height:72px;object-fit:cover;"
                             src="{{ $user->avatar_url ?? asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}" alt="preview">
                        <div class="ml-2">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="avatar" name="avatar"
                                       accept="image/*" capture="environment">
                                <label class="custom-file-label" for="avatar">Elegir foto</label>
                            </div>
                            @if($user->avatar_url)
                                <div class="custom-control custom-checkbox mt-2">
                                    <input type="checkbox" class="custom-control-input" id="remove_avatar" name="remove_avatar" value="1">
                                    <label class="custom-control-label" for="remove_avatar">Quitar foto actual</label>
                                </div>
                            @endif
                            <small class="text-muted d-block mt-1">
                                Acepta cámara en móvil. Máx 5MB (JPG/PNG/WEBP).
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3 d-flex justify-content-between">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <button class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar cambios
                </button>
            </div>
        </form>
    </x-adminlte-card>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.bsCustomFileInput) bsCustomFileInput.init();
    const input = document.getElementById('avatar');
    const img   = document.getElementById('avatarPreview');
    if (input && img) {
        input.addEventListener('change', () => {
            const f = input.files && input.files[0];
            if (!f) return;
            const url = URL.createObjectURL(f);
            img.src = url;
        });
    }
});
</script>
@endpush
