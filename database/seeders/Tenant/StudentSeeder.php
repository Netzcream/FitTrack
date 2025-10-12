<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Tenant\Student;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            [
                'first_name' => 'Juan',
                'last_name'  => 'Pérez',
                'email'      => 'juan@example.com',
                'phone'      => '+54 9 11 1234 5678',
                'status'     => 'active',
                'goal'       => 'hipertrofia',
            ],
            [
                'first_name' => 'María',
                'last_name'  => 'González',
                'email'      => 'maria@example.com',
                'phone'      => '+54 9 11 2345 6789',
                'status'     => 'active',
                'goal'       => 'pérdida de grasa',
            ],
            [
                'first_name' => 'Lucía',
                'last_name'  => 'Martínez',
                'email'      => 'lucia@example.com',
                'phone'      => '+54 9 11 3456 7890',
                'status'     => 'paused',
                'goal'       => 'mantenimiento',
            ],
            [
                'first_name' => 'Carlos',
                'last_name'  => 'Fernández',
                'email'      => 'carlos@example.com',
                'phone'      => '+54 9 11 4567 8901',
                'status'     => 'active',
                'goal'       => 'rendimiento deportivo',
            ],
            [
                'first_name' => 'Sofía',
                'last_name'  => 'López',
                'email'      => 'sofia@example.com',
                'phone'      => '+54 9 11 5678 9012',
                'status'     => 'prospect',
                'goal'       => 'salud general',
            ],
        ];

        foreach ($students as $s) {
            Student::updateOrCreate(
                ['email' => $s['email']],
                [
                    'uuid'                => Str::orderedUuid(),
                    'status'              => $s['status'],
                    'first_name'          => $s['first_name'],
                    'last_name'           => $s['last_name'],
                    'phone'               => $s['phone'],
                    'goal'                => $s['goal'],
                    'is_user_enabled'     => true,
                    'timezone'            => 'America/Argentina/Buenos_Aires',
                    'personal_data'       => [
                        'birth_date' => fake()->date('Y-m-d', '-20 years'),
                        'gender'     => fake()->randomElement(['male', 'female']),
                        'height_cm'  => fake()->numberBetween(160, 190),
                        'weight_kg'  => fake()->numberBetween(60, 95),
                    ],
                    'health_data'         => [
                        'injuries' => fake()->boolean(20) ? 'Lesión de hombro leve' : null,
                    ],
                    'training_data'       => [
                        'experience' => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
                        'days_per_week' => fake()->numberBetween(2, 5),
                    ],
                    'communication_data'  => [
                        'language' => 'es',
                        'notifications' => [
                            'session_reminder' => true,
                            'new_plan' => true,
                        ],
                    ],
                    'extra_data'          => [
                        'emergency_contact' => [
                            'name'  => fake()->name(),
                            'phone' => '+54 9 11 ' . fake()->numerify('#### ####'),
                        ],
                    ],
                ]
            );
        }
    }
}
