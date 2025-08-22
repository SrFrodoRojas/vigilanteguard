@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])
@section('title', 'Restablecer contraseña')

@section('auth_header')
    <p class="login-box-msg">
        Define tu nueva contraseña</p>
@endsection

@section('auth_body')
    @if ($errors->any())
        <x-adminlte-alert theme="danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ request()->route('token') }}">
        <input type="hidden" name="email" value="{{ request('email') }}">

        <div class="input-group mb-3">
            <input type="password" name="password" class="form-control" placeholder="Nueva contraseña" required>
            <div class="input-group-append">
                <div class="input-group-text"><span class="fas fa-lock"></span></div>
            </div>
        </div>

        <div class="input-group mb-3">
            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirmar contraseña"
                required>
            <div class="input-group-append">
                <div class="input-group-text"><span class="fas fa-lock"></span></div>
            </div>
        </div>

        <button class="btn btn-primary btn-block">Restablecer</button>
    </form>

@endsection

@section('auth_footer')
    <a href="{{ route('login') }}">Volver al login</a>
@endsection
