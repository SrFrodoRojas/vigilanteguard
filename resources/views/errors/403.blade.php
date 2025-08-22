@extends('adminlte::page')
@section('title', '403 — Acceso denegado')

@section('content_header')
    <h1 class="mb-0">Acceso denegado</h1>
    <small class="text-muted">User does not have the right permissions.</small>
@endsection

@section('content')
    <x-adminlte-alert theme="danger" icon="fas fa-ban" title="Sin permisos">
        No tenés permisos para acceder a esta sección.
    </x-adminlte-alert>

    <div class="card shadow-sm">
        <div class="card-body text-center">
            <p class="lead mb-2">
                Por seguridad, cerraremos tu sesión y te llevaremos al inicio de sesión en
                <strong id="countdown">10</strong> segundos…
            </p>

            <div class="mb-3">
                <small class="text-muted d-block">Si preferís, podés salir ahora mismo:</small>
            </div>

            <div class="d-flex flex-wrap justify-content-center gap-2">
                {{-- Logout inmediato (POST /logout) --}}
                <form id="logout-form" method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt"></i> Cerrar sesión ahora
                    </button>
                </form>

                {{-- Ir directo al login (sin cerrar sesión) --}}
                <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-sign-in-alt"></i> Ir al login
                </a>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
(function () {
    let secs = 10;
    const el = document.getElementById('countdown');

    function tick() {
        secs--;
        if (el) el.textContent = secs;
        if (secs <= 0) {
            autoLogout();
        } else {
            setTimeout(tick, 1000);
        }
    }
    setTimeout(tick, 1000);

    function autoLogout() {
        // Intento 1: cerrar sesión por fetch (POST), luego forzar redirección al login
        fetch("{{ route('logout') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "X-Requested-With": "XMLHttpRequest",
            },
            credentials: "same-origin",
        }).finally(() => {
            // Pase lo que pase, vamos al login
            window.location.replace("{{ route('login') }}");
        });
    }
})();
</script>
@endpush

@push('css')
<style>
    /* un poco de aire en móvil */
    @media (max-width: 767.98px) {
        .card-body { padding: 1rem; }
        .btn { margin: .15rem; }
    }
</style>
@endpush
