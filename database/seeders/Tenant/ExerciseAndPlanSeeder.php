<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Tenant\Exercise;
use App\Models\Tenant\TrainingPlan;

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
            $exerciseModels->push(
                Exercise::updateOrCreate(
                    ['name' => $e['name']],
                    [
                        'uuid'       => Str::uuid(),
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
            $plan = TrainingPlan::updateOrCreate(
                ['name' => $p['name']],
                [
                    'uuid'        => Str::uuid(),
                    'goal'        => $p['goal'],
                    'duration'    => $p['duration'],
                    'description' => $p['description'],
                    'is_active'   => true,
                    'meta'        => ['type' => 'template'],
                ]
            );

            // Asignar ejercicios al plan
            foreach ($p['structure'] as $dayData) {
                foreach ($dayData['exercises'] as $ex) {
                    $exercise = $exerciseModels->firstWhere('name', $ex['name']);
                    if ($exercise) {
                        $plan->exercises()->syncWithoutDetaching([
                            $exercise->id => [
                                'day'   => $dayData['day'],
                                'order' => $ex['order'],
                                'detail' => $ex['detail'],
                                'notes' => $ex['notes'],
                                'meta'  => json_encode(['source' => 'seed']),
                            ],
                        ]);
                    }
                }
            }
        }
    }
}
