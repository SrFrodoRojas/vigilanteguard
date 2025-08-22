@extends('adminlte::page')
@section('title','Roles')

@section('content_header')
    <h1>Roles</h1>
    <p class="text-muted mb-0">Cómo usar esta sección</p>
    <small class="text-muted">Aquí ves los roles disponibles. Usá <b>Editar permisos</b> para activar o desactivar qué puede hacer cada rol.</small>
@endsection

@section('content')
    @if(session('success'))
        <x-adminlte-alert theme="success">{{ session('success') }}</x-adminlte-alert>
    @endif
    @if($errors->any())
        <x-adminlte-alert theme="danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </x-adminlte-alert>
    @endif

    <x-adminlte-card theme="light" title="Listado de roles" icon="fas fa-user-shield">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width:1%;">#</th>
                        <th>Rol</th>
                        <th style="width:1%;">Permisos activos</th>
                        <th>Vista previa</th>
                        <th style="width:1%;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @php
                    $map = config('permissions_map', []);
                @endphp
                @forelse($roles as $role)
                    @php
                        // Lista de labels legibles
                        $permNames = $role->permissions->pluck('name')->toArray();
                        $activosLegibles = collect($permNames)->map(function ($p) use ($map) {
                            return $map[$p]['label'] ?? "Técnico: {$p}";
                        })->unique()->values();

                        $total      = $activosLegibles->count();
                        $previewN   = 4;
                        $preview    = $activosLegibles->take($previewN);
                        $restantes  = max(0, $total - $previewN);

                        $rid = 'roleperm_'.$role->id; // id para modal
                    @endphp
                    <tr>
                        <td>{{ $role->id }}</td>
                        <td class="text-capitalize">{{ $role->name }}</td>
                        <td class="text-center"><span class="badge badge-primary">{{ $total }}</span></td>
                        <td>
                            @if($total === 0)
                                <span class="text-muted">Sin permisos asignados</span>
                            @else
                                @foreach($preview as $lbl)
                                    <span class="badge badge-secondary mr-1 mb-1">{{ $lbl }}</span>
                                @endforeach
                                @if($restantes > 0)
                                    <button class="btn btn-xs btn-outline-info" data-toggle="modal" data-target="#{{ $rid }}">
                                        +{{ $restantes }} más
                                    </button>
                                @endif
                            @endif
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i> Editar permisos
                            </a>
                        </td>
                    </tr>

                    {{-- Modal con el detalle completo de permisos --}}
                    <div class="modal fade" id="{{ $rid }}" tabindex="-1" role="dialog" aria-labelledby="{{ $rid }}Label" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="{{ $rid }}Label">Permisos del rol: {{ $role->name }}</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    @forelse($activosLegibles as $lbl)
                                        <span class="badge badge-secondary mr-1 mb-1">{{ $lbl }}</span>
                                    @empty
                                        <span class="text-muted">Sin permisos.</span>
                                    @endforelse
                                </div>
                                <div class="modal-footer">
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-primary">Editar permisos</a>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr><td colspan="5">No hay roles.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Si es paginado, mostrará los links --}}
        @if(method_exists($roles, 'links'))
            <div class="mt-2">{{ $roles->links() }}</div>
        @endif
    </x-adminlte-card>
@endsection
