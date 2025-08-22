{{-- resources/views/admin/patrol/assignments/edit.blade.php --}}
@extends('adminlte::page')
@section('title', 'Editar Asignación')

@section('content_header')
    <h1>Editar Asignación #{{ $assignment->id }}</h1>
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

    <form method="POST" action="{{ route('admin.patrol.assignments.update', $assignment) }}">
        @csrf @method('PUT')

        <x-adminlte-select name="guard_id" label="Guardia" required>
            @foreach ($guards as $g)
                <option value="{{ $g->id }}" @selected(old('guard_id', $assignment->guard_id) == $g->id)>
                    {{ $g->name }} ({{ $g->email }})
                </option>
            @endforeach
        </x-adminlte-select>


        <x-adminlte-select name="patrol_route_id" label="Ruta" required>
            @foreach ($routes as $r)
                <option value="{{ $r->id }}" @selected(old('patrol_route_id', $assignment->patrol_route_id) == $r->id)>
                    {{ $r->name }} • {{ $r->branch->name ?? '-' }}
                </option>
            @endforeach
        </x-adminlte-select>

        <x-adminlte-input type="datetime-local" name="scheduled_start" label="Inicio programado"
            value="{{ old('scheduled_start', $assignment->scheduled_start->format('Y-m-d\TH:i')) }}" required />
        <x-adminlte-input type="datetime-local" name="scheduled_end" label="Fin programado"
            value="{{ old('scheduled_end', $assignment->scheduled_end->format('Y-m-d\TH:i')) }}" required />

        <x-adminlte-select name="status" label="Estado" required>
            @php $statuses = ['scheduled'=>'Programada','in_progress'=>'En curso','completed'=>'Completada','missed'=>'Perdida','cancelled'=>'Cancelada']; @endphp
            @foreach ($statuses as $val => $label)
                <option value="{{ $val }}" @selected(old('status', $assignment->status) === $val)>{{ $label }}</option>
            @endforeach
        </x-adminlte-select>

        <x-adminlte-button type="submit" class="mt-2" label="Actualizar" theme="primary" icon="fas fa-save" />
        <a href="{{ route('admin.patrol.assignments.index') }}" class="btn btn-outline-secondary mt-2">Volver</a>
    </form>
@endsection
