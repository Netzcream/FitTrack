<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds (tenant-level users).
     */
    public function run(): void
    {
        // --------------------------------------------
        // 3) Usuarios de ejemplo (opcionales por tenant)
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
    }
}
