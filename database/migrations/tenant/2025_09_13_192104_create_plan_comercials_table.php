<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('commercial_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('uuid')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('pricing')->nullable();
            $table->json('features')->nullable();
            $table->json('limits')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        DB::table('commercial_plans')->insert([
            [
                'uuid'        => Str::uuid(),
                'slug'        => 'online-basico',
                'name'        => 'Entrenamiento Básico Online',
                'description' => 'Seguimiento remoto con rutinas personalizadas y revisión semanal.',
                'is_active'   => true,
                'pricing'     => json_encode([
                    [
                        'type'     => 'monthly',
                        'amount'   => 8000,
                        'currency' => 'ARS',
                        'label'    => 'ARS 8.000 / mes',
                    ],
                ]),
                'features'    => json_encode([
                    'Rutinas personalizadas online',
                    'Revisión semanal por mensaje',
                    'Soporte por WhatsApp en horario laboral',
                    'Acceso al registro de métricas',
                ]),
                'limits'      => json_encode([
                    'sessions_per_week' => 3,
                    'video_calls'       => 0,
                    'in_person'         => false,
                ]),
                'order'       => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'uuid'        => Str::uuid(),
                'slug'        => 'premium-hibrido',
                'name'        => 'Entrenamiento Premium Híbrido',
                'description' => 'Combinación de sesiones presenciales y seguimiento online completo.',
                'is_active'   => true,
                'pricing'     => json_encode([
                    [
                        'type'     => 'monthly',
                        'amount'   => 18000,
                        'currency' => 'ARS',
                        'label'    => 'ARS 18.000 / mes',
                    ],
                ]),
                'features'    => json_encode([
                    '2 sesiones presenciales por semana',
                    'Rutinas y ajustes online ilimitados',
                    'Asistencia prioritaria',
                    'Reportes de progreso y gráficas mensuales',
                ]),
                'limits'      => json_encode([
                    'sessions_per_week' => 2,
                    'video_calls'       => 2,
                    'in_person'         => true,
                ]),
                'order'       => 2,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'uuid'        => Str::uuid(),
                'slug'        => 'elite-personal',
                'name'        => 'Plan Elite 1-a-1',
                'description' => 'Entrenamiento completamente personalizado con sesiones en vivo y soporte continuo.',
                'is_active'   => true,
                'pricing'     => json_encode([
                    [
                        'type'     => 'monthly',
                        'amount'   => 32000,
                        'currency' => 'ARS',
                        'label'    => 'ARS 32.000 / mes',
                    ],
                ]),
                'features'    => json_encode([
                    'Sesiones 1-a-1 en vivo',
                    'Evaluación física mensual',
                    'Plan nutricional integrado',
                    'Soporte prioritario 24/7',
                    'Acceso a métricas avanzadas',
                ]),
                'limits'      => json_encode([
                    'sessions_per_week' => 4,
                    'video_calls'       => 4,
                    'in_person'         => true,
                ]),
                'order'       => 3,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('commercial_plans');
    }
};
