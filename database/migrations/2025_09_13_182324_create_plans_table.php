<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
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


        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignId('commercial_plan_id')
                ->nullable()
                ->after('id')
                ->constrained('commercial_plans')
                ->nullOnDelete();
        });



        DB::table('commercial_plans')->insert([
            [
                'uuid'        => Str::uuid(),
                'slug'        => 'starter',
                'name'        => 'Starter',
                'description' => 'Hasta 5 alumnos, rutinas y mensajes, estadísticas básicas.',
                'is_active'   => true,
                'pricing'     => json_encode([
                    'monthly' => [
                        'amount'   => 0,
                        'currency' => 'ARS',
                        'label'    => 'Gratis',
                    ],
                ]),
                'features'    => json_encode([
                    'Hasta 5 alumnos',
                    'Rutinas y mensajes',
                    'Estadísticas básicas',
                ]),
                'limits'      => json_encode([
                    'students' => 5,
                    'trainers' => 1,
                ]),
                'order'       => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'uuid'        => Str::uuid(),
                'slug'        => 'pro',
                'name'        => 'Pro',
                'description' => 'Alumnos ilimitados, rutinas avanzadas, exportar estadísticas, branding y personalización.',
                'is_active'   => true,
                'pricing'     => json_encode([
                    'monthly' => [
                        'amount'   => 14900,
                        'currency' => 'ARS',
                        'label'    => 'ARS 14.900 / mes',
                    ],
                    'yearly' => [
                        'amount'   => 149000,
                        'currency' => 'ARS',
                        'label'    => 'ARS 149.000 / año',
                    ],
                ]),
                'features'    => json_encode([
                    'Alumnos ilimitados',
                    'Rutinas avanzadas',
                    'Exportar estadísticas',
                    'Branding y personalización',
                ]),
                'limits'      => json_encode([
                    'students' => null,
                    'trainers' => 3,
                ]),
                'order'       => 2,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'uuid'        => Str::uuid(),
                'slug'        => 'equipo',
                'name'        => 'Equipo',
                'description' => 'Para estudios/gyms, multi-entrenador, soporte prioritario.',
                'is_active'   => true,
                'pricing'     => json_encode([
                    'monthly' => [
                        'amount'   => 24900,
                        'currency' => 'ARS',
                        'label'    => 'ARS 24.900 / mes',
                    ],
                ]),
                'features'    => json_encode([
                    'Para estudios/gyms',
                    'Multi-entrenador',
                    'Soporte prioritario',
                ]),
                'limits'      => json_encode([
                    'students' => null,
                    'trainers' => 10,
                ]),
                'order'       => 3,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    public function down(): void
    {


        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'commercial_plan_id')) {
                $table->dropForeign(['commercial_plan_id']);
                $table->dropColumn('commercial_plan_id');
            }
        });


        Schema::dropIfExists('commercial_plans');
    }
};
