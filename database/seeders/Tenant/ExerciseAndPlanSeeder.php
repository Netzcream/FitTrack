<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Tenant\Exercise;
use App\Models\Tenant\TrainingPlan;
use App\Models\Tenant\Workout;
use App\Models\Tenant\Student;
use App\Services\Tenant\AssignPlanService;

class ExerciseAndPlanSeeder extends Seeder
{
    public function run(): void
    {
        // -------------------------------
        // 🏋️ 1️⃣ Catálogo de ejercicios base (26 total)
        // -------------------------------
        $exercises = [
            ['name' => 'Sentadilla con barra',     'category' => 'Piernas', 'level' => 'intermediate', 'equipment' => 'Barra'],
            ['name' => 'Press de banca',           'category' => 'Pecho',   'level' => 'intermediate', 'equipment' => 'Barra'],
            ['name' => 'Remo con barra',           'category' => 'Espalda', 'level' => 'intermediate', 'equipment' => 'Barra'],
            ['name' => 'Peso muerto',              'category' => 'Cuerpo completo', 'level' => 'advanced', 'equipment' => 'Barra'],
            ['name' => 'Plancha abdominal',        'category' => 'Core',    'level' => 'beginner', 'equipment' => 'Peso corporal'],
            ['name' => 'Zancadas con mancuernas',  'category' => 'Piernas', 'level' => 'intermediate', 'equipment' => 'Mancuernas'],

            // Nuevos ejercicios
            ['name' => 'Press militar',              'category' => 'Hombros', 'level' => 'intermediate', 'equipment' => 'Barra'],
            ['name' => 'Press inclinado con mancuernas', 'category' => 'Pecho', 'level' => 'intermediate', 'equipment' => 'Mancuernas'],
            ['name' => 'Dominadas',                  'category' => 'Espalda', 'level' => 'advanced', 'equipment' => 'Peso corporal'],
            ['name' => 'Curl de bíceps con barra',   'category' => 'Brazos', 'level' => 'beginner', 'equipment' => 'Barra'],
            ['name' => 'Extensiones de tríceps en polea', 'category' => 'Brazos', 'level' => 'beginner', 'equipment' => 'Polea'],
            ['name' => 'Abdominales crunch',         'category' => 'Core', 'level' => 'beginner', 'equipment' => 'Peso corporal'],
            ['name' => 'Press de piernas',           'category' => 'Piernas', 'level' => 'intermediate', 'equipment' => 'Máquina'],
            ['name' => 'Elevaciones laterales',      'category' => 'Hombros', 'level' => 'beginner', 'equipment' => 'Mancuernas'],
            ['name' => 'Remo en polea baja',         'category' => 'Espalda', 'level' => 'intermediate', 'equipment' => 'Máquina'],
            ['name' => 'Fondos en paralelas',        'category' => 'Brazos', 'level' => 'advanced', 'equipment' => 'Peso corporal'],
            ['name' => 'Burpees',                    'category' => 'Full body', 'level' => 'intermediate', 'equipment' => 'Peso corporal'],
            ['name' => 'Mountain climbers',          'category' => 'Cardio', 'level' => 'beginner', 'equipment' => 'Peso corporal'],
            ['name' => 'Step ups',                   'category' => 'Piernas', 'level' => 'beginner', 'equipment' => 'Banco'],
            ['name' => 'Peso muerto rumano',         'category' => 'Piernas', 'level' => 'intermediate', 'equipment' => 'Barra'],
            ['name' => 'Press Arnold',               'category' => 'Hombros', 'level' => 'intermediate', 'equipment' => 'Mancuernas'],
            ['name' => 'Curl concentrado',           'category' => 'Brazos', 'level' => 'beginner', 'equipment' => 'Mancuernas'],
            ['name' => 'Plancha lateral',            'category' => 'Core', 'level' => 'beginner', 'equipment' => 'Peso corporal'],
            ['name' => 'Remo invertido',             'category' => 'Espalda', 'level' => 'beginner', 'equipment' => 'Peso corporal'],
            ['name' => 'Press cerrado de banca',     'category' => 'Tríceps', 'level' => 'intermediate', 'equipment' => 'Barra'],
            ['name' => 'Saltos al cajón',            'category' => 'Full body', 'level' => 'intermediate', 'equipment' => 'Caja pliométrica'],
        ];

        $exerciseModels = collect();

        foreach ($exercises as $e) {
            $existingExercise = Exercise::withTrashed()
                ->where('name', $e['name'])
                ->first();

            // Respetar bajas lógicas: no re-crear ni reactivar.
            if ($existingExercise?->trashed()) {
                continue;
            }

            $exerciseModels->push(
                Exercise::updateOrCreate(
                    ['name' => $e['name']],
                    [
                        'uuid'       => $existingExercise?->uuid ?? Str::uuid(),
                        'category'   => $e['category'],
                        'level'      => $e['level'],
                        'equipment'  => $e['equipment'],
                        'is_active'  => true,
                    ]
                )
            );
        }

        // -------------------------------
        // 📋 2️⃣ Planes de entrenamiento base
        // -------------------------------
        $plans = [
            [
                'name' => 'Full Body Inicial',
                'goal' => 'Fuerza general',
                'duration' => '4 semanas',
                'description' => 'Plan de cuerpo completo 3 veces por semana, ideal para principiantes.',
                'structure' => [
                    ['day' => 1, 'exercises' => [
                        ['name' => 'Sentadilla con barra', 'detail' => '4x10', 'notes' => 'Enfocar en técnica', 'order' => 1],
                        ['name' => 'Press de banca',       'detail' => '4x8',  'notes' => 'Controlar la barra', 'order' => 2],
                        ['name' => 'Plancha abdominal',    'detail' => '3x60s', 'notes' => 'Mantener línea corporal', 'order' => 3],
                    ]],
                    ['day' => 2, 'exercises' => [
                        ['name' => 'Peso muerto',          'detail' => '4x6',  'notes' => 'Cuidar lumbar', 'order' => 1],
                        ['name' => 'Remo con barra',       'detail' => '4x10', 'notes' => 'Tracción controlada', 'order' => 2],
                    ]],
                ],
            ],
            [
                'name' => 'Hipertrofia intermedia',
                'goal' => 'Aumento de masa muscular',
                'duration' => '8 semanas',
                'description' => 'Rutina dividida por grupos musculares con volumen moderado.',
                'structure' => [
                    ['day' => 1, 'exercises' => [
                        ['name' => 'Press de banca',          'detail' => '5x10', 'notes' => 'Ritmo 2-1-2', 'order' => 1],
                        ['name' => 'Zancadas con mancuernas', 'detail' => '4x12', 'notes' => 'Alternar piernas', 'order' => 2],
                    ]],
                    ['day' => 2, 'exercises' => [
                        ['name' => 'Sentadilla con barra',    'detail' => '5x8',  'notes' => 'Progresar peso', 'order' => 1],
                        ['name' => 'Plancha abdominal',       'detail' => '3x90s', 'notes' => 'Control respiración', 'order' => 2],
                    ]],
                ],
            ],
            [
                'name' => 'Condicionamiento avanzado',
                'goal' => 'Resistencia y fuerza',
                'duration' => '6 semanas',
                'description' => 'Plan intenso orientado al rendimiento y control del RPE.',
                'structure' => [
                    ['day' => 1, 'exercises' => [
                        ['name' => 'Peso muerto',       'detail' => '5x5', 'notes' => 'RPE 8', 'order' => 1],
                        ['name' => 'Remo con barra',    'detail' => '4x10', 'notes' => 'Control postural', 'order' => 2],
                    ]],
                    ['day' => 2, 'exercises' => [
                        ['name' => 'Sentadilla con barra', 'detail' => '5x5', 'notes' => 'RPE 7', 'order' => 1],
                        ['name' => 'Plancha abdominal',   'detail' => '3x60s', 'notes' => 'Activar core', 'order' => 2],
                    ]],
                ],
            ],
        ];

        foreach ($plans as $p) {
            $existingPlan = TrainingPlan::withTrashed()
                ->where('name', $p['name'])
                ->first();

            // Respetar bajas lógicas: no re-crear ni reactivar.
            if ($existingPlan?->trashed()) {
                continue;
            }

            $plan = TrainingPlan::updateOrCreate(
                ['name' => $p['name']],
                [
                    'uuid'        => $existingPlan?->uuid ?? Str::uuid(),
                    'goal'        => $p['goal'],
                    'duration'    => $p['duration'],
                    'description' => $p['description'],
                    'is_active'   => true,
                    'meta'        => ['type' => 'template'],
                ]
            );

            // Asignar ejercicios al plan (estructura JSON en exercises_data)
            $exercisesData = [];
            foreach ($p['structure'] as $dayData) {
                foreach ($dayData['exercises'] as $ex) {
                    $exercise = $exerciseModels->firstWhere('name', $ex['name']);
                    if ($exercise) {
                        $exercisesData[] = [
                            'exercise_id' => $exercise->id,
                            'name'        => $exercise->name,
                            'day'         => $dayData['day'],
                            'order'       => $ex['order'],
                            'detail'      => $ex['detail'],
                            'notes'       => $ex['notes'],
                            'meta'        => ['source' => 'seed'],
                        ];
                    }
                }
            }

            $plan->update(['exercises_data' => $exercisesData]);
        }

        // -------------------------------
        // 💪 3️⃣ Workouts de ejemplo (usando nueva estructura JSON)
        // -------------------------------
        // Solo para tenants nuevos: si ya hay workouts, no agregar datos de ejemplo.
        if (Workout::query()->exists()) {
            return;
        }

        $students = Student::limit(3)->get();

        if ($students->isNotEmpty()) {
            // Obtener algunos planes y ejercicios
            $fullBodyPlan = TrainingPlan::where('name', 'Full Body Inicial')->first();
            $hipertrofiaPlan = TrainingPlan::where('name', 'Hipertrofia intermedia')->first();

            $assigner = new AssignPlanService();
            $assignmentByStudent = [];
            $startsAt = now()->subDays(21);
            $endsAt = now()->addDays(7);

            $studentOne = $students->get(0);
            $studentTwo = $students->get(1);
            $studentThree = $students->get(2);

            if ($studentOne && $fullBodyPlan) {
                $assignmentByStudent[$studentOne->id] = $assigner->assign(
                    $fullBodyPlan,
                    $studentOne,
                    $startsAt,
                    $endsAt,
                    true
                );
            }

            if ($studentTwo && $hipertrofiaPlan) {
                $assignmentByStudent[$studentTwo->id] = $assigner->assign(
                    $hipertrofiaPlan,
                    $studentTwo,
                    $startsAt,
                    $endsAt,
                    true
                );
            }

            if ($studentThree && $fullBodyPlan) {
                $assignmentByStudent[$studentThree->id] = $assigner->assign(
                    $fullBodyPlan,
                    $studentThree,
                    $startsAt,
                    $endsAt,
                    true
                );
            }

            $assignmentOne = $studentOne ? ($assignmentByStudent[$studentOne->id] ?? null) : null;
            $assignmentTwo = $studentTwo ? ($assignmentByStudent[$studentTwo->id] ?? null) : null;
            $assignmentThree = $studentThree ? ($assignmentByStudent[$studentThree->id] ?? null) : null;

            // Ejercicios de ejemplo
            $sentadilla = $exerciseModels->firstWhere('name', 'Sentadilla con barra');
            $pressBanca = $exerciseModels->firstWhere('name', 'Press de banca');
            $pesoMuerto = $exerciseModels->firstWhere('name', 'Peso muerto');
            $remoBarra = $exerciseModels->firstWhere('name', 'Remo con barra');
            $plancha = $exerciseModels->firstWhere('name', 'Plancha abdominal');

            // Workout 1: Sesión completa de un estudiante
            // Note: In production, workouts should be created via WorkoutOrchestrationService
            if ($studentOne && $assignmentOne && $sentadilla && $pressBanca && $plancha) {
                Workout::create([
                    'student_id' => $studentOne->id,
                    'student_plan_assignment_id' => $assignmentOne->id,
                    'plan_day' => 1,
                    'sequence_index' => 1,
                    'cycle_index' => 1,
                    'started_at' => now()->subDays(7)->subMinutes(65),
                    'completed_at' => now()->subDays(7),
                    'duration_minutes' => 65,
                    'status' => 'completed',
                    'notes' => 'Excelente sesión, buena técnica',
                    'rating' => 5,
                    'exercises_data' => [
                        [
                            'exercise_id' => $sentadilla->id,
                            'exercise_name' => $sentadilla->name,
                            'sets_completed' => 4,
                            'reps_per_set' => [10, 10, 10, 8],
                            'weight_used_kg' => 60.0,
                            'duration_seconds' => null,
                            'rest_time_seconds' => 90,
                            'notes' => 'Buena profundidad',
                            'completed_at' => now()->subDays(7)->format('Y-m-d H:i:s'),
                            'order' => 1,
                        ],
                        [
                            'exercise_id' => $pressBanca->id,
                            'exercise_name' => $pressBanca->name,
                            'sets_completed' => 4,
                            'reps_per_set' => [8, 8, 7, 6],
                            'weight_used_kg' => 70.0,
                            'duration_seconds' => null,
                            'rest_time_seconds' => 120,
                            'notes' => 'Últimas series pesadas',
                            'completed_at' => now()->subDays(7)->format('Y-m-d H:i:s'),
                            'order' => 2,
                        ],
                        [
                            'exercise_id' => $plancha->id,
                            'exercise_name' => $plancha->name,
                            'sets_completed' => 3,
                            'reps_per_set' => [],
                            'weight_used_kg' => null,
                            'duration_seconds' => 180, // 3x60s = 180s total
                            'rest_time_seconds' => 45,
                            'notes' => 'Core estable',
                            'completed_at' => now()->subDays(7)->format('Y-m-d H:i:s'),
                            'order' => 3,
                        ],
                    ],
                ]);
            }

            // Workout 2: Sesión de otro estudiante
            if ($studentTwo && $assignmentTwo && $pesoMuerto && $remoBarra) {
                Workout::create([
                    'student_id' => $studentTwo->id,
                    'student_plan_assignment_id' => $assignmentTwo->id,
                    'plan_day' => 1,
                    'sequence_index' => 1,
                    'cycle_index' => 1,
                    'started_at' => now()->subDays(5)->subMinutes(55),
                    'completed_at' => now()->subDays(5),
                    'duration_minutes' => 55,
                    'status' => 'completed',
                    'notes' => 'Día de espalda intenso',
                    'rating' => 4,
                    'exercises_data' => [
                        [
                            'exercise_id' => $pesoMuerto->id,
                            'exercise_name' => $pesoMuerto->name,
                            'sets_completed' => 5,
                            'reps_per_set' => [5, 5, 5, 5, 5],
                            'weight_used_kg' => 100.0,
                            'duration_seconds' => null,
                            'rest_time_seconds' => 180,
                            'notes' => 'Peso récord personal',
                            'completed_at' => now()->subDays(5)->format('Y-m-d H:i:s'),
                            'order' => 1,
                        ],
                        [
                            'exercise_id' => $remoBarra->id,
                            'exercise_name' => $remoBarra->name,
                            'sets_completed' => 4,
                            'reps_per_set' => [10, 10, 10, 9],
                            'weight_used_kg' => 50.0,
                            'duration_seconds' => null,
                            'rest_time_seconds' => 90,
                            'notes' => 'Buena contracción',
                            'completed_at' => now()->subDays(5)->format('Y-m-d H:i:s'),
                            'order' => 2,
                        ],
                    ],
                ]);
            }

            // Workout 3: Sesión reciente sin plan asignado
            if ($studentThree && $assignmentThree && $sentadilla && $plancha) {
                Workout::create([
                    'student_id' => $studentThree->id,
                    'student_plan_assignment_id' => $assignmentThree->id,
                    'plan_day' => 1,
                    'sequence_index' => 1,
                    'cycle_index' => 1,
                    'started_at' => now()->subDays(1)->subMinutes(40),
                    'completed_at' => now()->subDays(1),
                    'duration_minutes' => 40,
                    'status' => 'completed',
                    'notes' => 'Sesión express',
                    'rating' => 3,
                    'exercises_data' => [
                        [
                            'exercise_id' => $sentadilla->id,
                            'exercise_name' => $sentadilla->name,
                            'sets_completed' => 3,
                            'reps_per_set' => [12, 12, 10],
                            'weight_used_kg' => 50.0,
                            'duration_seconds' => null,
                            'rest_time_seconds' => 60,
                            'notes' => 'Sesión rápida',
                            'completed_at' => now()->subDays(1)->format('Y-m-d H:i:s'),
                            'order' => 1,
                        ],
                        [
                            'exercise_id' => $plancha->id,
                            'exercise_name' => $plancha->name,
                            'sets_completed' => 3,
                            'reps_per_set' => [],
                            'weight_used_kg' => null,
                            'duration_seconds' => 120, // 3x40s
                            'rest_time_seconds' => 30,
                            'notes' => null,
                            'completed_at' => now()->subDays(1)->format('Y-m-d H:i:s'),
                            'order' => 2,
                        ],
                    ],
                ]);
            }

            // Workout 4: Ejemplo de workout anterior
            if ($studentOne && $assignmentOne && $sentadilla && $pressBanca) {
                Workout::create([
                    'student_id' => $studentOne->id,
                    'student_plan_assignment_id' => $assignmentOne->id,
                    'plan_day' => 2,
                    'sequence_index' => 2,
                    'cycle_index' => 1,
                    'started_at' => now()->subDays(14)->subMinutes(60),
                    'completed_at' => now()->subDays(14),
                    'duration_minutes' => 60,
                    'status' => 'completed',
                    'rating' => 4,
                    'exercises_data' => [
                        [
                            'exercise_id' => $sentadilla->id,
                            'exercise_name' => $sentadilla->name,
                            'sets_completed' => 3,
                            'reps_per_set' => [10, 10, 10],
                            'weight_used_kg' => 55.0,
                            'rest_time_seconds' => 90,
                            'order' => 1,
                        ],
                        [
                            'exercise_id' => $pressBanca->id,
                            'exercise_name' => $pressBanca->name,
                            'sets_completed' => 3,
                            'reps_per_set' => [8, 8, 8],
                            'weight_used_kg' => 65.0,
                            'rest_time_seconds' => 120,
                            'order' => 2,
                        ],
                    ],
                ]);
            }
        }
    }
}
