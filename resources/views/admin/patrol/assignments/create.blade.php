{{-- resources/views/admin/patrol/assignments/create.blade.php --}}
@extends('adminlte::page')
@section('title','Nueva Asignación')

@section('content_header')
    <h1>Nueva Asignación</h1>
@endsection

@section('content')

    @if ($errors->any())
        <x-adminlte-alert theme="danger" title="Error">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    @if (session('success'))
        <x-adminlte-alert theme="success" title="OK">
            {{ session('success') }}
        </x-adminlte-alert>
    @endif

    <form method="POST" action="{{ route('admin.patrol.assignments.store') }}">
        @csrf

        <x-adminlte-select name="guard_id" label="Guardia" required>
            <option value="" disabled selected>— Seleccionar guardia —</option>
            @forelse($guards as $g)
                <option value="{{ $g->id }}" @selected(old('guard_id')==$g->id)>
                    {{ $g->name }} ({{ $g->email }})
                </option>
            @empty
                {{-- Si no hay guardias, mostramos un aviso abajo --}}
            @endforelse
        </x-adminlte-select>
        @if($guards->isEmpty())
            <x-adminlte-alert theme="warning" title="Sin guardias disponibles">
                No hay usuarios con rol <code>guard</code> ni permiso <code>patrol.scan</code>.
                Asigná alguno desde <a href="{{ route('admin.users.index') }}">Usuarios</a> o corré el comando de permisos.
            </x-adminlte-alert>
        @endif

        <x-adminlte-select name="patrol_route_id" label="Ruta" required>
            <option value="" disabled selected>— Seleccionar ruta —</option>
            @forelse($routes as $r)
                <option value="{{ $r->id }}" @selected(old('patrol_route_id')==$r->id)>
                    {{ $r->name }} • {{ $r->branch->name ?? '-' }}
                </option>
            @empty
                {{-- manejar sin rutas si aplica --}}
            @endforelse
        </x-adminlte-select>

        <x-adminlte-input type="datetime-local" name="scheduled_start" label="Inicio programado"
            value="{{ old('scheduled_start') }}" required/>
        <x-adminlte-input type="datetime-local" name="scheduled_end" label="Fin programado"
            value="{{ old('scheduled_end') }}" required/>

        <x-adminlte-button type="submit" class="mt-2" label="Guardar" theme="primary" icon="fas fa-save" />
    </form>

@endsection
