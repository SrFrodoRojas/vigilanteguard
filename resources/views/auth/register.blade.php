@extends('adminlte::auth.auth-page', ['auth_type' => 'register'])

@section('title', 'Crear cuenta | Vigilante')

@section('auth_header')
    <img src="{{ asset('logo.png') }}" alt="Vigilante" width="56" height="56" class="mb-2" style="border-radius:14px">
    <h1 class="h4 mb-0">Crear cuenta</h1>
    <small class="text-muted">Acceso al panel de Vigilante</small>
@endsection

@section('auth_body')
    <form action="{{ route('register') }}" method="POST">
        @csrf

        <div class="input-group mb-3">
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name') }}" placeholder="Nombre completo" autofocus>
            <div class="input-group-append"><div class="input-group-text"><span class="fas fa-user"></span></div></div>
            @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
        </div>

        <div class="input-group mb-3">
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" placeholder="Correo electrónico">
            <div class="input-group-append"><div class="input-group-text"><span class="fas fa-envelope"></span></div></div>
            @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
        </div>

        <div class="input-group mb-3">
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                   placeholder="Contraseña">
            <div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>
            @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
        </div>

        <div class="input-group mb-3">
            <input type="password" name="password_confirmation" class="form-control"
                   placeholder="Confirmar contraseña">
            <div class="input-group-append"><div class="input-group-text"><span class="fas fa-check"></span></div></div>
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="background:#dc408a;border-color:#dc408a">
            Registrarme
        </button>
    </form>
@endsection

@section('auth_footer')
    <div>¿Ya tenés cuenta? <a href="{{ route('login') }}">Ingresar</a></div>
@endsection
