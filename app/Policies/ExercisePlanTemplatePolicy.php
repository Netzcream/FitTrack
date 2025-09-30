<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Tenant\Exercise\ExercisePlanTemplate;
use Illuminate\Auth\Access\Response;

class ExercisePlanTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        // El tenant boundary lo impone el scope global; no hace falta chequear acá.
        return true;
    }

    public function view(User $user, ExercisePlanTemplate $template): bool
    {
        // Con el scope multitenant activo, alcanza con permitir la vista.
        return true;
    }

    public function instantiate(User $user, ExercisePlanTemplate $template): bool
    {

        //return $user->hasAnyRole(['owner','coach']) || $user->can('exercise.plans.instantiate');
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ExercisePlanTemplate $exercisePlanTemplate): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ExercisePlanTemplate $exercisePlanTemplate): bool
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ExercisePlanTemplate $exercisePlanTemplate): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ExercisePlanTemplate $exercisePlanTemplate): bool
    {
        return true;
    }
}
