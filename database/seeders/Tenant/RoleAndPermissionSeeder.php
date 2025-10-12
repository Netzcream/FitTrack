<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds (tenant-level).
     */
    public function run(): void
    {
        // --------------------------------------------
        // 1️⃣ Permisos base (ordenados por área funcional)
        // --------------------------------------------
        $permissions = [
            // Administración general
            'gestionar usuarios',
            'gestionar roles',
            'gestionar contactos',

            // Entrenamientos y contenido
            'gestionar entrenamientos',
            'gestionar ejercicios',
            'gestionar recursos',

            // Alumnos
            'ver alumnos',
            'editar alumnos',
            'eliminar alumnos',

            // Comunicación
            'enviar mensajes',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // --------------------------------------------
        // 2️⃣ Roles del tenant y sus permisos
        // --------------------------------------------
        $roles = [
            'Admin' => [
                'gestionar usuarios',
                'gestionar roles',
                'gestionar contactos',
                'gestionar entrenamientos',
                'gestionar ejercicios',
                'gestionar recursos',
                'ver alumnos',
                'editar alumnos',
                'eliminar alumnos',
                'enviar mensajes',
            ],

            'Asistente' => [
                'gestionar entrenamientos',
                'gestionar ejercicios',
                'gestionar contactos',
                'ver alumnos',
                'editar alumnos',
                'enviar mensajes',
            ],

            'Entrenador' => [
                'gestionar entrenamientos',
                'gestionar ejercicios',
                'ver alumnos',
                'editar alumnos',
            ],

            'Alumno' => [
                'ver alumnos', // solo su propio perfil
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }

        // --------------------------------------------
        // 3️⃣ Usuarios de ejemplo (opcionales por tenant)
        // --------------------------------------------
        $users = [
            [
                'name' => 'Administrador',
                'email' => 'admin@fittrack.com.ar',
                'role' => 'Admin',
            ],
            [
                'name' => 'Asistente',
                'email' => 'asistente@fittrack.com.ar',
                'role' => 'Asistente',
            ],
        ];

        foreach ($users as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make('4y8Zi_9f&7Nx'),
                ]
            );

            if (! $user->hasRole($u['role'])) {
                $user->assignRole($u['role']);
            }
        }

        // --------------------------------------------
        // ✅ Resumen en consola (opcional)
        // --------------------------------------------
        $this->command->info('Roles y permisos creados para el tenant:');
        foreach ($roles as $role => $perms) {
            $this->command->line("- {$role}: " . implode(', ', $perms));
        }
    }
}
