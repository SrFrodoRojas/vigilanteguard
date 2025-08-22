@php
    $rawBranchColor = optional($user->branch)->color;
    if ($rawBranchColor && preg_match('/^#([A-Fa-f0-9]{3}){1,2}$/', $rawBranchColor)) {
        $borderColor = $rawBranchColor;
        $bgColor = $rawBranchColor . '20';
    } else {
        $hue = ($user->branch_id ?? 0) * 30 % 360;
        $borderColor = "hsl({$hue},70%,35%)";
        $bgColor     = "hsl({$hue},70%,95%)";
    }
    $isActive = (bool) $user->is_active;
    $isAdmin  = $user->hasRole('admin');
    $branchName = $user->branch->name ?? 'Sin sucursal';
    $wa = $user->whatsapp_url;
    $phone = $user->phone;
    $lastSeen = $user->last_seen_humans ?? null;
    $canDelete = $user->email !== 'admin@admin.com' && (!($isAdminSection ?? false) || !($isAdmin && ($adminsCount ?? 0) <= 1));
@endphp

<div class="user-card mb-2" style="background-color: {{ $bgColor }}; border-left: 4px solid {{ $borderColor }};">
    <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            @if($user->avatar_url)
                <img src="{{ $user->avatar_url }}" alt="Foto de {{ $user->name }}" class="avatar-img mr-2">
            @else
                <div class="avatar-circle mr-2" title="{{ $user->name }}">
                    {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                </div>
            @endif
            <div>
                <div class="font-weight-600">
                    {{ $user->name }}
                    @if($isActive)
                        <span class="badge badge-success align-middle ml-1">Activo</span>
                    @else
                        <span class="badge badge-danger align-middle ml-1">Inactivo</span>
                    @endif
                </div>
                <div class="small text-muted">{{ $user->email }}</div>
                @if($phone)
                    <div class="small mt-1">
                        @if($wa)
                            <a href="{{ $wa }}" target="_blank" class="text-success" title="WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>&nbsp;
                        @endif
                        <span class="text-muted"><i class="fas fa-phone-alt"></i></span>
                        <span>{{ $phone }}</span>
                    </div>
                @endif
                @if($lastSeen)
                    <div class="small text-muted mt-1"><i class="far fa-clock"></i> Última vez activo: {{ $lastSeen }}</div>
                @endif
            </div>
        </div>

        <div class="text-right">
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning btn-sm mr-1">
                <i class="fas fa-edit"></i> Editar
            </a>
            @if($canDelete)
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar usuario?')">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="mt-2 d-flex flex-wrap align-items-center">
        @foreach ($user->roles as $role)
            <span class="badge badge-info mr-1 mb-1">{{ $role->name }}</span>
        @endforeach
        <span class="badge mr-1 mb-1" style="background-color: {{ $borderColor }}; color:#fff;">
            <i class="fas fa-warehouse"></i> {{ $branchName }}
        </span>
    </div>
</div>
@push('css')
<style>
    .avatar-circle {
        width: 32px; height: 32px; border-radius: 50%;
        background: #e9ecef; color: #495057; display:flex; align-items:center; justify-content:center;
        font-weight:700; font-size:.9rem;
    }
    .avatar-img {
        width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 1px solid rgba(0,0,0,.08);
    }
    .user-card {
        border: 1px solid rgba(0,0,0,.06);
        border-radius: .6rem;
        padding: .75rem .8rem;
        box-shadow: 0 1px 2px rgba(0,0,0,.03);
    }
    .font-weight-600 { font-weight: 600; }
</style>
@endpush
