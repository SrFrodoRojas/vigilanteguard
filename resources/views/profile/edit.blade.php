@extends('adminlte::page')
@section('title', 'Perfil')

@section('content_header')
    <h1>
        Mi perfil</h1>
@endsection

@section('content')
    @if (session('status'))
        <x-adminlte-alert theme="success">{{ session('status') }}</x-adminlte-alert>
    @endif
    @if ($errors->any())
        <x-adminlte-alert theme="danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    <div class="row">
        <div class="col-md-6">
            <x-adminlte-card title="Información de la cuenta" theme="primary" icon="fas fa-user-cog">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf @method('PATCH')

                    <div class="form-group">
                        <label>Nombre</label>
                        <input name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input name="email" type="email" class="form-control"
                            value="{{ old('email', auth()->user()->email) }}" required>
                    </div>

                    <button class="btn btn-primary">Guardar cambios</button>
                </form>
            </x-adminlte-card>
        </div>

        <div class="col-md-6">
            <x-adminlte-card title="Cambiar contraseña" theme="secondary" icon="fas fa-key">
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label>Contraseña actual</label>
                        <input name="current_password" type="password" class="form-control" required
                            autocomplete="current-password">
                    </div>

                    <div class="form-group">
                        <label>Nueva contraseña</label>
                        <input name="password" type="password" class="form-control" required autocomplete="new-password">
                    </div>

                    <div class="form-group">
                        <label>Confirmar nueva contraseña</label>
                        <input name="password_confirmation" type="password" class="form-control" required
                            autocomplete="new-password">
                    </div>

                    <button class="btn btn-secondary">Actualizar contraseña</button>
                </form>
            </x-adminlte-card>

            <x-adminlte-card title="Eliminar cuenta" theme="danger" icon="fas fa-user-times">
                <form method="POST" action="{{ route('profile.destroy') }}"
                    onsubmit="return confirm('¿Seguro que deseas eliminar tu cuenta? Esta acción no se puede deshacer.');">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger">Eliminar mi cuenta</button>
                </form>
            </x-adminlte-card>
        </div>
    </div>
@endsection
