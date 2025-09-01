<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1) Permisos (ordenados por área)
        $permissions = [

            'gestionar usuarios',
            'gestionar roles',
            'gestionar recursos',
            'gestionar entrenamientos',
            'gestionar ejercicios',
            'gestionar contactos',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2) Roles y permisos
        $roles = [
            'Admin' => [
                'gestionar usuarios',
                'gestionar roles',
                'gestionar entrenamientos',
                'gestionar ejercicios',
                'gestionar recursos',
                'gestionar contactos',

            ],


            'Asistente' => [
                'gestionar entrenamientos',
                'gestionar ejercicios',
                'gestionar contactos',

            ],


        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }

        // 3) Usuarios de ejemplo (opcionales)
        $admin = User::firstOrCreate(
            ['email' => 'admin@fittrack.com.ar'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('4y8Zi_9f&7Nx'),
            ]
        );
        if (! $admin->hasRole('Admin')) {
            $admin->assignRole('Admin');
        }

        $admin = User::firstOrCreate(
            ['email' => 'asistente@fittrack.com.ar'],
            [
                'name' => 'Asistente',
                'password' => Hash::make('4y8Zi_9f&7Nx'),
            ]
        );
        if (! $admin->hasRole('Asistente')) {
            $admin->assignRole('Asistente');
        }
    }
}
