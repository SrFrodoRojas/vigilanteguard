<?php

namespace App\Policies;

use App\Models\PatrolAssignment;
use App\Models\User;

class PatrolAssignmentPolicy
{
    /**
     * Admin/Administrador todo acceso.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->can('patrol.manage')) {
            return true;
        }
        return null;
    }

    /**
     * Ver asignación (si fuera necesario).
     */
    public function view(User $user, PatrolAssignment $assignment): bool
    {
        return $assignment->guard_id === $user->id;
    }

    /**
     * Actualizar/operar la asignación (start/finish/scan).
     */
    public function update(User $user, PatrolAssignment $assignment): bool
    {
        return $assignment->guard_id === $user->id;
    }
}
