<?php

namespace Tests\Feature\Tenant\Livewire;

use App\Livewire\Tenant\Users\Form as UsersForm;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserFormTest extends TestCase
{
    public function test_it_syncs_only_direct_permissions_outside_selected_role(): void
    {
        $rolePermission = Permission::findOrCreate('ver reportes');
        $extraPermission = Permission::findOrCreate('editar metricas');

        $role = Role::findOrCreate('Entrenador');
        $role->givePermissionTo($rolePermission);

        $user = User::factory()->create();

        Livewire::test(UsersForm::class, ['user' => $user])
            ->set('name', 'Usuario Entrenador')
            ->set('email', $user->email)
            ->set('role', 'Entrenador')
            ->set('directPermissionIds', [$rolePermission->id, $extraPermission->id])
            ->call('save')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertTrue($user->hasRole('Entrenador'));
        $this->assertTrue($user->hasPermissionTo('ver reportes'));
        $this->assertTrue($user->hasDirectPermission('editar metricas'));
        $this->assertFalse($user->hasDirectPermission('ver reportes'));
    }
}
