{{-- resources/views/admin/patrol/checkpoints/index.blade.php --}}
@extends('adminlte::page')
@section('title','Checkpoints')

@section('content_header')
    <h1>Checkpoints • {{ $route->name }}</h1>
@endsection

@section('content')
<x-adminlte-button label="Nuevo checkpoint" theme="primary" icon="fas fa-plus" class="mb-3"
    onclick="window.location='{{ route('admin.patrol.routes.checkpoints.create',$route) }}'"/>

<x-adminlte-card>
    <table class="table table-hover">
        <thead><tr>
            <th>ID</th><th>Nombre</th><th>Lat</th><th>Lng</th><th>Radio</th><th>QR</th><th>Acciones</th>
        </tr></thead>
        <tbody>
        @foreach($checkpoints as $cp)
            <tr>
                <td>{{ $cp->id }}</td>
                <td>{{ $cp->name }}</td>
                <td>{{ $cp->latitude }}</td>
                <td>{{ $cp->longitude }}</td>
                <td>{{ $cp->radius_m }} m</td>
                <td>
                    <a href="{{ route('admin.patrol.checkpoints.qr', $cp) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                        Ver QR
                    </a>
                </td>
                <td>
                    <a class="btn btn-sm btn-info" href="{{ route('admin.patrol.checkpoints.edit',$cp) }}">Editar</a>
                    <form action="{{ route('admin.patrol.checkpoints.destroy',$cp) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Eliminar checkpoint?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</x-adminlte-card>
@endsection
