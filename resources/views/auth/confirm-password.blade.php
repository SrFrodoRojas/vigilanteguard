@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])
@section('title', 'Confirmar contraseña')

@section('auth_header')
    <p class="login-box-msg">Confirma tu contraseña para continuar</p>
@endsection

@section('auth_body')
    @if ($errors->any())
        <x-adminlte-alert theme="danger">
            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </x-adminlte-alert>
    @endif

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="input-group mb-3">
            <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
            <div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>
        </div>
        <button class="btn btn-primary btn-block">Confirmar</button>
    </form>
@endsection
