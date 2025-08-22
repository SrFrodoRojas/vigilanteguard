<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SetupPatrolPermissions extends Command
{
    protected $signature = 'vigilante:setup-patrol-permissions
                            {--assign= : Asignar a roles separados por coma (ej: admin,administrador,guard)}';
    protected $description = 'Crea permisos del módulo de patrullas y (opcional) los asigna a roles';

    public function handle(): int
    {
        $perms = ['patrol.manage', 'patrol.view', 'patrol.scan'];

        foreach ($perms as $name) {
            Permission::findOrCreate($name, 'web');
            $this->info("OK permiso: {$name}");
        }

        if ($rolesOpt = $this->option('assign')) {
            $roles = array_filter(array_map('trim', explode(',', $rolesOpt)));
            foreach ($roles as $rname) {
                $role = Role::firstOrCreate(['name' => $rname, 'guard_name' => 'web']);
                // Asignación por defaults típicos:
                $rmap  = ['administrador' => 'admin', 'guardia' => 'guard'];
                $rname = $rmap[$rname] ?? $rname;

                if ($rname === 'admin') {
                    $role->givePermissionTo(['patrol.manage', 'patrol.view', 'patrol.scan']);
                    $this->info("→ {$rname}: manage+view+scan");
                } elseif ($rname === 'guard') {
                    $role->givePermissionTo(['patrol.view', 'patrol.scan']);
                    $this->info("→ {$rname}: view+scan");
                } else {
                    // Por si tenés otro rol custom
                    $role->givePermissionTo(['patrol.view']);
                    $this->info("→ {$rname}: view");
                }
            }
        }

        // Refrescar cache de Spatie
        $this->callSilent('permission:cache-reset');
        $this->info('Permisos listos y cache reseteado.');
        return self::SUCCESS;
    }
}
