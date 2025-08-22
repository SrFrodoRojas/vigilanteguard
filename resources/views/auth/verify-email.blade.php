@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])
@section('title', 'Verificar email')

@section('auth_header')
    <p class="login-box-msg">Verifica tu correo electrónico</p>
@endsection

@section('auth_body')
    @if (session('status') == 'verification-link-sent')
        <x-adminlte-alert theme="success">Se envió un nuevo enlace de verificación a tu email.</x-adminlte-alert>
    @endif

    <p class="mb-3">Antes de continuar, revisa tu email para el enlace de verificación.</p>

    <form method="POST" action="{{ route('verification.send') }}" class="mb-2">
        @csrf
        <button class="btn btn-primary btn-block">Reenviar enlace de verificación</button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="btn btn-outline-secondary btn-block">Cerrar sesión</button>
    </form>
@endsection
