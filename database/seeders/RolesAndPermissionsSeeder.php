<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Limpia cache de permisos por si acaso
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // --- Permisos ---
        $perms = [
            // ACCESOS
            'access.enter',        // Registrar entradas
            'access.exit',         // Registrar salidas
            'access.view',         // Ver listado general
            'access.view.active',  // Ver activos (dentro)
            'access.show',         // Ver detalle

            // REPORTES
            'reports.view',

            // ADMIN
            'roles.manage',
            'users.manage',
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        // --- Roles ---
        $admin   = Role::firstOrCreate(['name' => 'admin']);
        $guardia = Role::firstOrCreate(['name' => 'guardia']);

        // Admin: todo
        $admin->syncPermissions($perms);

        // Guardia: lo operativo
        $guardia->syncPermissions([
            'access.enter',
            'access.exit',
            'access.view',
            'access.view.active',
            'access.show',
            // si querés que el guardia vea reportes, agrega 'reports.view' aquí
        ]);

        // --- Asignar rol al usuario 1 (si existe) ---
        $u1 = User::find(1);
        if ($u1 && !$u1->hasRole('admin')) {
            $u1->syncRoles(['admin']);
        }

        // Refresca cache
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
