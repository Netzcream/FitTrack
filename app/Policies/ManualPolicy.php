<?php

namespace App\Policies;

use App\Models\Central\Manual;
use App\Models\User;

class ManualPolicy
{
    /**
     * Determine if the user can view any manuals.
     */
    public function viewAny(User $user): bool
    {
        // Todos los usuarios autenticados pueden ver los manuales
        return true;
    }

    /**
     * Determine if the user can view the manual.
     */
    public function view(User $user, Manual $manual): bool
    {
        // Todos pueden ver manuales publicados y activos
        // Los administradores pueden ver todos
        return $user->hasRole('super_admin') ||
               ($manual->is_active && $manual->published_at !== null);
    }

    /**
     * Determine if the user can create manuals.
     */
    public function create(User $user): bool
    {
        // Solo super admins pueden crear manuales
        return $user->hasRole('super_admin');
    }

    /**
     * Determine if the user can update the manual.
     */
    public function update(User $user, Manual $manual): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine if the user can delete the manual.
     */
    public function delete(User $user, Manual $manual): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine if the user can restore the manual.
     */
    public function restore(User $user, Manual $manual): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine if the user can permanently delete the manual.
     */
    public function forceDelete(User $user, Manual $manual): bool
    {
        return $user->hasRole('super_admin');
    }
}
