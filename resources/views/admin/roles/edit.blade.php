@extends('adminlte::page')
@section('title', 'Editar permisos')

@section('content_header')
    <h1>
        Editar permisos — <span class="text-capitalize">{{ $role->name }}</span></h1>
    <a href="{{ route('admin.roles.index') }}" class="btn btn-sm btn-outline-secondary mt-2">
        <i class="fas fa-arrow-left"></i> Volver a roles
    </a>
@endsection

@section('content')
    @if (session('success'))
        <x-adminlte-alert theme="success">{{ session('success') }}</x-adminlte-alert>
    @endif
    @if ($errors->any())
        <x-adminlte-alert theme="danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    @php
        $map = config('permissions_map', []);
        // Traemos todas las permissions (si no las inyectaste desde el controlador)
        $perms = \Spatie\Permission\Models\Permission::orderBy('name')->get();
        $rolePermNames = $role->permissions->pluck('name')->all();
        $rolePermSet = array_fill_keys($rolePermNames, true);

        // Agrupar por 'group' del mapa
        $agrupados = $perms->groupBy(function ($p) use ($map) {
            return $map[$p->name]['group'] ?? 'OTROS';
        });
    @endphp

    <form method="POST" action="{{ route('admin.roles.update', $role) }}">
        @csrf
        @method('PUT')

        @foreach ($agrupados as $grupo => $items)
            <x-adminlte-card title="{{ $grupo }}" theme="light" icon="fas fa-layer-group" collapsible>
                <div class="d-flex justify-content-end mb-2">
                    <div class="btn-group btn-group-sm" role="group" aria-label="Marcar grupo">
                        <button type="button" class="btn btn-outline-primary js-check-group"
                            data-group="{{ \Illuminate\Support\Str::slug($grupo) }}" data-check="1">
                            Marcar todo
                        </button>
                        <button type="button" class="btn btn-outline-secondary js-check-group"
                            data-group="{{ \Illuminate\Support\Str::slug($grupo) }}" data-check="0">
                            Desmarcar todo
                        </button>
                    </div>
                </div>

                <div class="row">
                    @foreach ($items as $perm)
                        @php
                            $slug = \Illuminate\Support\Str::slug($grupo);
                            $label = $map[$perm->name]['label'] ?? "Técnico: {$perm->name}";
                            $desc = $map[$perm->name]['desc'] ?? null;
                            $checked = isset($rolePermSet[$perm->name]);
                        @endphp
                        <div class="col-12 col-md-6">
                            <div class="custom-control custom-checkbox mb-2" data-group="{{ $slug }}">
                                <input type="checkbox" class="custom-control-input" id="perm_{{ $perm->id }}"
                                    name="permissions[]" value="{{ $perm->name }}" {{ $checked ? 'checked' : '' }}>
                                <label class="custom-control-label" for="perm_{{ $perm->id }}">
                                    {{ $label }}
                                </label>
                                @if ($desc)
                                    <div class="text-muted small ml-4">{{ $desc }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-adminlte-card>
        @endforeach

        <div class="mt-3">
            <button class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar cambios
            </button>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
@endsection

@push('js')
    <script>
        // Marcar/Desmarcar todo por grupo
        document.querySelectorAll('.js-check-group').forEach(btn => {
            btn.addEventListener('click', () => {
                const group = btn.getAttribute('data-group');
                const check = btn.getAttribute('data-check') === '1';
                document.querySelectorAll('[data-group="' + group + '"] input[type="checkbox"]').forEach(
                    chk => {
                        chk.checked = check;
                    });
            });
        });
    </script>
@endpush
