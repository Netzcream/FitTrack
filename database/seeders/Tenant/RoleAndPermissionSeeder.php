<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds (tenant-level).
     */
    public function run(): void
    {
        // --------------------------------------------
        // 1) Permisos base (ordenados por area funcional)
        // --------------------------------------------
        $permissions = [
            // Administracion general
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

            // Comunicacion
            'enviar mensajes',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // --------------------------------------------
        // 2) Roles del tenant y sus permisos
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
                'gestionar contactos',
                'ver alumnos',
                'editar alumnos',
                'enviar mensajes',
            ],

            'Entrenador' => [
                'gestionar entrenamientos',
                'gestionar ejercicios',
                'gestionar contactos',
                'ver alumnos',
                'editar alumnos',
                'enviar mensajes',
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
        // Resumen en consola (opcional)
        // --------------------------------------------
        $this->command->info('Roles y permisos creados para el tenant:');
        foreach ($roles as $role => $perms) {
            $this->command->line("- {$role}: " . implode(', ', $perms));
        }
    }
}
