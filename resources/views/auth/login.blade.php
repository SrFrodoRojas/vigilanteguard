@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('title', 'Ingresar | Vigilante')

@section('auth_header')
    <img src="{{ asset('logo.png') }}" alt="Vigilante" width="56" height="56" class="mb-2" style="border-radius:14px">
    <h1 class="h4 mb-0">Bienvenido a <strong>Vigilante</strong></h1>
    <small class="text-muted">Control de entradas y salidas</small>
@endsection

@section('auth_body')
    <form action="{{ route('login') }}" method="POST">
        @csrf

        <div class="input-group mb-3">
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" placeholder="Correo electrónico" autofocus>
            <div class="input-group-append">
                <div class="input-group-text"><span class="fas fa-envelope"></span></div>
            </div>
            @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
        </div>

        <div class="input-group mb-3">
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                   placeholder="Contraseña">
            <div class="input-group-append">
                <div class="input-group-text"><span class="fas fa-lock"></span></div>
            </div>
            @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
        </div>

        <div class="row align-items-center">
            <div class="col-6">
                <div class="icheck-primary">
                    <input type="checkbox" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">Recordarme</label>
                </div>
            </div>
            <div class="col-6 text-right">
                <button type="submit" class="btn btn-primary btn-block"
                        style="background:#dc408a;border-color:#dc408a">
                    Ingresar
                </button>
            </div>
        </div>
    </form>
@endsection

@section('auth_footer')
    @if (Route::has('password.request'))
        <a class="btn btn-link px-0" href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
    @endif
    @if (Route::has('register'))
        <div class="mt-2">¿No tenés cuenta?
            <a href="{{ route('register') }}">Crear una</a>
        </div>
    @endif
@endsection

@push('css')
<style>
  body.login-page {
    background:
      radial-gradient(800px 400px at 20% -10%, #ffe4f0 0, rgba(255,255,255,0) 60%) no-repeat,
      radial-gradient(900px 500px at 120% 10%, #ffd2e9 0, rgba(255,255,255,0) 60%) no-repeat,
      #ffffff;
  }
  .login-card-body { border-radius: 14px; box-shadow: 0 10px 24px rgba(0,0,0,.06); }
  .btn-primary:hover { filter: brightness(1.05); }
</style>
@endpush
