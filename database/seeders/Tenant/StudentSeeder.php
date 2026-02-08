<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Tenant\Student;
use App\Models\User;
use Spatie\Permission\Models\Role;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $studentRole = Role::firstOrCreate(['name' => 'Alumno']);

        $students = [
            [
                'first_name' => 'Juan',
                'last_name'  => 'Perez',
                'email'      => 'juan@example.com',
                'phone'      => '+54 9 11 1234 5678',
                'status'     => 'active',
                'goal'       => 'Hipertrofia',
            ],
            [
                'first_name' => 'Maria',
                'last_name'  => 'Gonzalez',
                'email'      => 'maria@example.com',
                'phone'      => '+54 9 11 2345 6789',
                'status'     => 'active',
                'goal'       => 'Perdida de grasa',
            ],
            [
                'first_name' => 'Lucia',
                'last_name'  => 'Martinez',
                'email'      => 'lucia@example.com',
                'phone'      => '+54 9 11 3456 7890',
                'status'     => 'paused',
                'goal'       => 'Mantenimiento',
            ],
            [
                'first_name' => 'Carlos',
                'last_name'  => 'Fernandez',
                'email'      => 'carlos@example.com',
                'phone'      => '+54 9 11 4567 8901',
                'status'     => 'active',
                'goal'       => 'Rendimiento deportivo',
            ],
            [
                'first_name' => 'Sofia',
                'last_name'  => 'Lopez',
                'email'      => 'sofia@example.com',
                'phone'      => '+54 9 11 5678 9012',
                'status'     => 'prospect',
                'goal'       => 'Salud general',
            ],
        ];

        foreach ($students as $s) {
            $user = User::firstOrCreate(
                ['email' => $s['email']],
                [
                    'name' => trim($s['first_name'] . ' ' . $s['last_name']),
                    'password' => Str::random(20),
                ]
            );
            if (! $user->hasRole($studentRole)) {
                $user->assignRole($studentRole);
            }

            Student::updateOrCreate(
                ['email' => $s['email']],
                [
                    'uuid'                => Str::orderedUuid(),
                    'user_id'             => $user->id,
                    'status'              => $s['status'],
                    'first_name'          => $s['first_name'],
                    'last_name'           => $s['last_name'],
                    'phone'               => $s['phone'],
                    'goal'                => $s['goal'],
                    'is_user_enabled'     => true,
                    'billing_frequency'   => 'monthly',
                    'account_status'      => 'on_time',
                    'data'                => $this->buildStudentData($s),
                ]
            );
        }
    }

    private function buildStudentData(array $student): array
    {
        $seed = abs(crc32($student['email']));
        $contactNames = [
            'Ana Perez',
            'Carlos Rodriguez',
            'Marta Lopez',
            'Diego Fernandez',
            'Laura Gomez',
        ];

        return [
            'birth_date' => now()
                ->subYears(20 + ($seed % 16))
                ->subDays(($seed >> 5) % 365)
                ->format('Y-m-d'),
            'gender' => $seed % 2 === 0 ? 'male' : 'female',
            'height_cm' => 160 + ($seed % 31),
            'weight_kg' => 60 + (($seed >> 8) % 36),
            'injuries' => $seed % 5 === 0 ? 'Lesion leve de hombro' : null,
            'notifications' => [
                'session_reminder' => true,
                'new_plan' => true,
            ],
            'emergency_contact' => [
                'name' => $contactNames[$seed % count($contactNames)],
                'phone' => sprintf(
                    '+54 9 11 %04d %04d',
                    1000 + ($seed % 9000),
                    1000 + (($seed >> 12) % 9000)
                ),
            ],
        ];
    }
}
