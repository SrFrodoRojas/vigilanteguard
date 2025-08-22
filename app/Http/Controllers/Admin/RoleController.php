<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function edit(Role $role)
    {
        $map       = config('permissions_map'); // <- el archivo de arriba
        $rolePerms = $role->permissions->pluck('name')->toArray();
        $groups    = collect($map)->map(function ($v, $perm) {
            $v['perm'] = $perm;
            return $v;
        })->groupBy('group');

        return view('admin.roles.edit', compact('role', 'groups', 'rolePerms'));

    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()
            ->route('admin.roles.edit', $role)
            ->with('success', 'Permisos actualizados para el rol: ' . $role->name);
    }
}
