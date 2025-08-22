@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])
@section('title', 'Recuperar contraseña')

@section('auth_header')
    <p class="login-box-msg">Te enviaremos un enlace de recuperación</p>
@endsection

@section('auth_body')
    @if (session('status'))
        <x-adminlte-alert theme="success">{{ session('status') }}</x-adminlte-alert>
    @endif
    @if ($errors->any())
        <x-adminlte-alert theme="danger">
            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </x-adminlte-alert>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="input-group mb-3">
            <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Email" required>
            <div class="input-group-append"><div class="input-group-text"><span class="fas fa-envelope"></span></div></div>
        </div>
        <button class="btn btn-primary btn-block">Enviar enlace</button>
    </form>
@endsection

@section('auth_footer')
    <a href="{{ route('login') }}">Volver al login</a>
@endsection
