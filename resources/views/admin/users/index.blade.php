@extends('adminlte::page')
@section('title', 'Usuarios')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap">
        <div class="mb-2 mb-md-0">
            <h1 class="mb-0">Usuarios</h1>
            <small class="text-muted">Administradores y Guardias con filtros independientes</small>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <a href="#admins" class="btn btn-outline-info mr-2 mb-2">
                <i class="fas fa-user-shield"></i> Ir a Administradores
            </a>
            <a href="#guards" class="btn btn-outline-primary mr-2 mb-2">
                <i class="fas fa-user-lock"></i> Ir a Guardias
            </a>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary mb-2">
                <i class="fas fa-user-plus"></i> Nuevo usuario
            </a>
        </div>
    </div>
@endsection

@section('content')
    @if (session('success'))
        <x-adminlte-alert theme="success" class="mb-3">
            <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
        </x-adminlte-alert>
    @endif

    @if ($errors->any())
        <x-adminlte-alert theme="danger" class="mb-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    {{-- ==================== ADMINISTRADORES ==================== --}}
    <div id="admins" class="card shadow-sm mb-4">
        <div class="card-header bg-gradient-info d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="fas fa-user-shield mr-2"></i>
                <h3 class="card-title mb-0">Administradores</h3>
                <span class="badge badge-light ml-2">{{ $admins->total() }}</span>
            </div>

            <div class="d-flex align-items-center">
                <small class="text-white-50 d-none d-md-inline mr-2">No se puede eliminar al último admin</small>
                <button class="btn btn-sm btn-outline-light d-inline d-md-none" type="button" data-toggle="collapse"
                    data-target="#adminFilters" aria-expanded="false" aria-controls="adminFilters">
                    <i class="fas fa-sliders-h mr-1"></i>Filtros
                </button>
            </div>
        </div>

        <div class="card-body">

            {{-- Filtros Admin --}}
            <form method="GET" id="adminFilters" class="form-row mb-3 collapse d-md-flex show">
                {{-- Mantener parámetros de la otra sección (guardias) --}}
                <input type="hidden" name="search_guard" value="{{ request('search_guard') }}">
                <input type="hidden" name="branch_guard" value="{{ request('branch_guard') }}">
                <input type="hidden" name="status_guard" value="{{ request('status_guard') }}">
                <input type="hidden" name="guards_page" value="{{ request('guards_page') }}">

                <div class="col-12 col-md-4 mb-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" name="search_admin" class="form-control"
                            placeholder="Buscar por nombre o email" value="{{ request('search_admin') }}">
                    </div>
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <select name="branch_admin" class="form-control form-control-sm">
                        <option value="">Todas las sucursales</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}"
                                {{ (string) request('branch_admin') === (string) $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2 mb-2">
                    <select name="status_admin" class="form-control form-control-sm">
                        <option value="">Estado (todos)</option>
                        <option value="active" {{ request('status_admin') === 'active' ? 'selected' : '' }}>Activos
                        </option>
                        <option value="inactive" {{ request('status_admin') === 'inactive' ? 'selected' : '' }}>Inactivos
                        </option>
                    </select>
                </div>

                <div class="col-6 col-md-3 mb-2 text-right">
                    <button type="submit" class="btn btn-primary btn-sm mb-1">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm mb-1">
                        <i class="fas fa-sync"></i> Limpiar
                    </a>
                </div>
            </form>

            {{-- ======= Móvil (cards) ======= --}}
            <div class="d-block d-md-none">
                @forelse($admins as $user)
                    @include('admin.users.partials.user-card', [
                        'user' => $user,
                        'adminsCount' => $adminsCount,
                        'isAdminSection' => true,
                    ])
                @empty
                    <div class="text-center text-muted py-3">No se encontraron administradores</div>
                @endforelse

                <div class="mt-2">
                    {{ $admins->appends(request()->except('admins_page'))->links() }}
                </div>
            </div>

            {{-- ======= Desktop (tabla) ======= --}}
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover table-striped table-sm">
                    <thead class="thead-light sticky-top">
                        <tr>
                            <th style="min-width: 180px">Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Rol</th>
                            <th>Sucursal</th>
                            <th>Estado</th>
                            <th class="text-right" style="width: 120px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $user)
                            @php
                                $baseHue = $user->branch_id ? ($user->branch_id * 37) % 360 : 210;
                                $fallback = "hsl({$baseHue}, 75%, 94%)";
                                $rowBg = optional($user->branch)->color ?: $fallback;
                                $badgeBg = optional($user->branch)->color ?: "hsl({$baseHue}, 60%, 35%)";
                                $phone = $user->phone ?? null;
                                $wa = $phone ? 'https://wa.me/' . preg_replace('/\D+/', '', $phone) : null;
                            @endphp
                            <tr style="background-color: {{ $rowBg }};">
                                <td class="align-middle">
                                    <div class="d-flex align-items-center">
                                        @if ($user->avatar_url)
                                            <img src="{{ $user->avatar_url }}" alt="Foto de {{ $user->name }}"
                                                class="avatar-img mr-2"
                                                style="width:28px;height:28px;object-fit:cover;border-radius:50%;border:1px solid rgba(0,0,0,.08);">
                                        @else
                                            <div class="avatar-circle mr-2" title="{{ $user->name }}">
                                                {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <span>{{ $user->name }}</span>

                                    </div>
                                </td>
                                <td class="align-middle">{{ $user->email }}</td>
                                <td class="align-middle">
                                    @if ($phone)
                                        <a href="{{ $wa }}" target="_blank" class="text-success"
                                            title="WhatsApp">
                                            <i class="fab fa-whatsapp"></i> {{ $phone }}
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    @foreach ($user->roles as $role)
                                        <span class="badge badge-info">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td class="align-middle">
                                    @if ($user->branch)
                                        <span class="badge" style="background-color: {{ $badgeBg }}; color: #fff;">
                                            {{ $user->branch->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    @if ($user->is_active)
                                        <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Activo</span>
                                    @else
                                        <span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Inactivo</span>
                                    @endif
                                </td>
                                <td class="align-middle text-right">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning btn-sm"
                                        title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    @if ($user->email !== 'admin@admin.com' && !($user->hasRole('admin') && $adminsCount <= 1))
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                            class="d-inline">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-danger btn-sm"
                                                onclick="return confirm('¿Eliminar usuario?')" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No se encontraron administradores</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-2">
                    {{ $admins->onEachSide(1)->appends(request()->except('admins_page'))->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== GUARDIAS ==================== --}}
    <div id="guards" class="card shadow-sm">
        <div class="card-header bg-gradient-primary d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="fas fa-user-lock mr-2"></i>
                <h3 class="card-title mb-0">Guardias</h3>
                <span class="badge badge-light ml-2">{{ $guards->total() }}</span>
            </div>

            <button class="btn btn-sm btn-outline-light d-inline d-md-none" type="button" data-toggle="collapse"
                data-target="#guardFilters" aria-expanded="false" aria-controls="guardFilters">
                <i class="fas fa-sliders-h mr-1"></i>Filtros
            </button>
        </div>

        <div class="card-body">
            {{-- Filtros Guardias --}}
            <form method="GET" id="guardFilters" class="form-row mb-3 collapse d-md-flex show">
                {{-- Mantener parámetros de admins al filtrar guardias --}}
                <input type="hidden" name="search_admin" value="{{ request('search_admin') }}">
                <input type="hidden" name="branch_admin" value="{{ request('branch_admin') }}">
                <input type="hidden" name="status_admin" value="{{ request('status_admin') }}">
                <input type="hidden" name="admins_page" value="{{ request('admins_page') }}">

                <div class="col-12 col-md-4 mb-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" name="search_guard" class="form-control"
                            placeholder="Buscar por nombre o email" value="{{ request('search_guard') }}">
                    </div>
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <select name="branch_guard" class="form-control form-control-sm">
                        <option value="">Todas las sucursales</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}"
                                {{ (string) request('branch_guard') === (string) $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2 mb-2">
                    <select name="status_guard" class="form-control form-control-sm">
                        <option value="">Estado (todos)</option>
                        <option value="active" {{ request('status_guard') === 'active' ? 'selected' : '' }}>Activos
                        </option>
                        <option value="inactive" {{ request('status_guard') === 'inactive' ? 'selected' : '' }}>Inactivos
                        </option>
                    </select>
                </div>

                <div class="col-6 col-md-3 mb-2 text-right">
                    <button type="submit" class="btn btn-primary btn-sm mb-1">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm mb-1">
                        <i class="fas fa-sync"></i> Limpiar
                    </a>
                </div>
            </form>

            {{-- ======= Móvil (cards) ======= --}}
            <div class="d-block d-md-none">
                @forelse($guards as $user)
                    @include('admin.users.partials.user-card', [
                        'user' => $user,
                        'isAdminSection' => false, // no aplica restricción de "último admin"
                    ])
                @empty
                    <div class="text-center text-muted py-3">No se encontraron guardias</div>
                @endforelse

                <div class="mt-2">
                    {{ $guards->appends(request()->except('guards_page'))->links() }}
                </div>
            </div>

            {{-- ======= Desktop (tabla) ======= --}}
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover table-striped table-sm">
                    <thead class="thead-light sticky-top">
                        <tr>
                            <th style="min-width: 180px">Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Rol</th>
                            <th>Sucursal</th>
                            <th>Estado</th>
                            <th class="text-right" style="width: 120px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($guards as $user)
                            @php
                                $baseHue = $user->branch_id ? ($user->branch_id * 37) % 360 : 210;
                                $fallback = "hsl({$baseHue}, 75%, 94%)";
                                $rowBg = optional($user->branch)->color ?: $fallback;
                                $badgeBg = optional($user->branch)->color ?: "hsl({$baseHue}, 60%, 35%)";
                                $phone = $user->phone ?? null;
                                $wa = $phone ? 'https://wa.me/' . preg_replace('/\D+/', '', $phone) : null;
                            @endphp
                            <tr style="background-color: {{ $rowBg }};">
                                <td class="align-middle">
                                    <div class="d-flex align-items-center">
                                        @if ($user->avatar_url)
                                            <img src="{{ $user->avatar_url }}" alt="Foto de {{ $user->name }}"
                                                class="avatar-img mr-2"
                                                style="width:28px;height:28px;object-fit:cover;border-radius:50%;border:1px solid rgba(0,0,0,.08);">
                                        @else
                                            <div class="avatar-circle mr-2" title="{{ $user->name }}">
                                                {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <span>{{ $user->name }}</span>

                                    </div>
                                </td>
                                <td class="align-middle">{{ $user->email }}</td>
                                <td class="align-middle">
                                    @if ($phone)
                                        <a href="{{ $wa }}" target="_blank" class="text-success"
                                            title="WhatsApp">
                                            <i class="fab fa-whatsapp"></i> {{ $phone }}
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    @foreach ($user->roles as $role)
                                        <span class="badge badge-info">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td class="align-middle">
                                    @if ($user->branch)
                                        <span class="badge" style="background-color: {{ $badgeBg }}; color:#fff;">
                                            {{ $user->branch->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    @if ($user->is_active)
                                        <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Activo</span>
                                    @else
                                        <span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Inactivo</span>
                                    @endif
                                </td>
                                <td class="align-middle text-right">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning btn-sm"
                                        title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                        class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('¿Eliminar usuario?')" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No se encontraron guardias</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-2">
                    {{ $guards->onEachSide(1)->appends(request()->except('guards_page'))->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        /* Headers pegajosos en desktop */
        .thead-light.sticky-top {
            top: 0;
            z-index: 1;
        }

        /* Avatar inicial */
        .avatar-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e9ecef;
            color: #495057;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .9rem;
        }

        /* Card móvil para cada usuario */
        .user-card {
            border: 1px solid rgba(0, 0, 0, .06);
            border-radius: .6rem;
            padding: .75rem .8rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .03);
        }

        .font-weight-600 {
            font-weight: 600;
        }

        /* Hover filas desktop */
        .table tbody tr:hover {
            filter: brightness(96%);
        }

        /* Botones compactos en móvil */
        @media (max-width: 767.98px) {
            .btn {
                padding: .3rem .5rem;
                font-size: .85rem;
            }

            .input-group-text,
            .form-control-sm {
                font-size: .85rem;
            }
        }

        /* Avatar inicial */
        .avatar-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e9ecef;
            color: #495057;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .9rem;
        }

        /* Card móvil de usuario */
        .user-card {
            border: 1px solid rgba(0, 0, 0, .06);
            border-radius: .6rem;
            padding: .75rem .8rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .03);
        }

        .font-weight-600 {
            font-weight: 600;
        }

        /* Botones compactos en móvil */
        @media (max-width: 767.98px) {
            .btn {
                padding: .3rem .5rem;
                font-size: .85rem;
            }

            .input-group-text,
            .form-control-sm {
                font-size: .85rem;
            }

            .user-card {
                padding: .7rem;
            }
        }
    </style>
@endpush
