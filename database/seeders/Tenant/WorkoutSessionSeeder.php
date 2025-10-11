<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use App\Models\Tenant\WorkoutSession;
use App\Models\Tenant\WorkoutSessionSet;

class WorkoutSessionSeeder extends Seeder
{
    public function run(): void
    {
        // Crear 10 sesiones con sets relacionados
        WorkoutSession::factory()
            ->count(10)
            ->has(
                WorkoutSessionSet::factory()
                    ->count(rand(3, 8)),
                'sets'
            )
            ->create();

        $this->command->info('✅ Workout sessions & sets seeded successfully.');
    }
}
