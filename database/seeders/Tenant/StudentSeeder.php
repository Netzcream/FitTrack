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

        // Obtener el email del administrador del tenant actual
        $adminEmail = tenancy()->tenant?->admin_email ?? 'admin@example.com';

        $students = [
            [
                'first_name' => 'Juan',
                'last_name'  => 'Perez',
                'phone'      => '+54 9 11 1234 5678',
                'status'     => 'active',
                'goal'       => 'Hipertrofia',
            ],
            [
                'first_name' => 'Maria',
                'last_name'  => 'Gonzalez',
                'phone'      => '+54 9 11 2345 6789',
                'status'     => 'active',
                'goal'       => 'Perdida de grasa',
            ],
            [
                'first_name' => 'Lucia',
                'last_name'  => 'Martinez',
                'phone'      => '+54 9 11 3456 7890',
                'status'     => 'paused',
                'goal'       => 'Mantenimiento',
            ],
            [
                'first_name' => 'Carlos',
                'last_name'  => 'Fernandez',
                'phone'      => '+54 9 11 4567 8901',
                'status'     => 'active',
                'goal'       => 'Rendimiento deportivo',
            ],
            [
                'first_name' => 'Sofia',
                'last_name'  => 'Lopez',
                'phone'      => '+54 9 11 5678 9012',
                'status'     => 'prospect',
                'goal'       => 'Salud general',
            ],
        ];

        foreach ($students as $s) {
            // Generar email con plus addressing basado en el email del admin
            $studentEmail = $this->generatePlusAddressingEmail($adminEmail, $s['first_name'], $s['last_name']);
            $s['email'] = $studentEmail;

            $existingStudent = Student::withTrashed()
                ->where('email', $studentEmail)
                ->first();

            // Respetar bajas lógicas: no re-crear ni reactivar.
            if ($existingStudent?->trashed()) {
                continue;
            }

            $user = User::firstOrCreate(
                ['email' => $studentEmail],
                [
                    'name' => trim($s['first_name'] . ' ' . $s['last_name']),
                    'password' => Str::random(20),
                ]
            );

            if (! $user->hasRole($studentRole)) {
                $user->assignRole($studentRole);
            }

            Student::updateOrCreate(
                ['email' => $studentEmail],
                [
                    'uuid'                => $existingStudent?->uuid ?? Str::orderedUuid(),
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

    /**
     * Genera un email con plus addressing.
     *
     * Ejemplo: admin@example.com + Carlos Sanchez = admin+csanchez@example.com
     *
     * @param string $baseEmail Email base del administrador
     * @param string $firstName Primer nombre del alumno
     * @param string $lastName Apellido del alumno
     * @return string Email con plus addressing
     */
    private function generatePlusAddressingEmail(string $baseEmail, string $firstName, string $lastName): string
    {
        // Separar email en local part y domain
        if (!str_contains($baseEmail, '@')) {
            return strtolower($firstName . '@example.com');
        }

        [$localPart, $domain] = explode('@', $baseEmail, 2);

        // Crear alias: primera letra del nombre + apellido completo
        $firstInitial = mb_substr($firstName, 0, 1);
        $alias = $firstInitial . $lastName;

        // Normalizar: quitar acentos, convertir a minúsculas, quitar caracteres especiales
        $alias = $this->normalizeAlias($alias);

        return $localPart . '+' . $alias . '@' . $domain;
    }

    /**
     * Normaliza un alias removiendo acentos y caracteres especiales.
     *
     * @param string $text Texto a normalizar
     * @return string Texto normalizado
     */
    private function normalizeAlias(string $text): string
    {
        // Convertir a minúsculas
        $text = mb_strtolower($text, 'UTF-8');

        // Reemplazar caracteres acentuados
        $unwanted = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'ñ' => 'n',
        ];
        $text = strtr($text, $unwanted);

        // Remover todo excepto letras y números
        $text = preg_replace('/[^a-z0-9]/', '', $text);

        return $text;
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
