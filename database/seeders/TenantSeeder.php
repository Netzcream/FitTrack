<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // Permisos y roles

        $this->call([
            RoleAndPermissionSeeder::class
        ]);




    }
}
