<?php

namespace Database\Seeders;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {


        // Crear o encontrar el rol Admin con todos los permisos
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $permissions = Permission::all();
        $adminRole->syncPermissions($permissions);

        // Crear o encontrar el usuario Admin y asignarle el rol
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@fittrack.com.ar'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('4y8Zi_9f&7Nx'), // Contraseña genérica
            ]
        );
        $adminUser->assignRole($adminRole);
        User::firstOrCreate(['email' => 'user@fittrack.com.ar'],['name' => 'User','password' => Hash::make('password123')]);
    }
}
