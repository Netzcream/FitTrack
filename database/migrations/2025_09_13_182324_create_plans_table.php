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


        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description');
            $table->double('price'); // sin manejo de moneda por ahora
            $table->json('data')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });


        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignId('plan_id')
                ->nullable()
                ->after('id')
                ->constrained('plans')
                ->nullOnDelete();
        });



        DB::table('plans')->insert([
            [
                'uuid'        => Str::uuid(),
                'code'        => 'starter',
                'name'        => 'Starter',
                'description' => 'Hasta 5 alumnos, rutinas y mensajes, estadísticas básicas',
                'price'       => 0,
                'data'        => json_encode([
                    'features' => [
                        'Hasta 5 alumnos',
                        'Rutinas y mensajes',
                        'Estadísticas básicas',
                    ],
                ]),
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'uuid'        => Str::uuid(),
                'code'        => 'pro',
                'name'        => 'Pro',
                'description' => 'Alumnos ilimitados, rutinas avanzadas, exportar estadísticas, branding y personalización',
                'price'       => 14900,
                'data'        => json_encode([
                    'features' => [
                        'Alumnos ilimitados',
                        'Rutinas avanzadas',
                        'Exportar estadísticas',
                        'Branding y personalización',
                    ],
                ]),
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'uuid'        => Str::uuid(),
                'code'        => 'equipo',
                'name'        => 'Equipo',
                'description' => 'Para estudios/gyms, multi-entrenador, soporte prioritario',
                'price'       => 24900,
                'data'        => json_encode([
                    'features' => [
                        'Para estudios/gyms',
                        'Multi-entrenador',
                        'Soporte prioritario',
                    ],
                ]),
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    public function down(): void
    {


        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'plan_id')) {
                $table->dropForeign(['plan_id']);
                $table->dropColumn('plan_id');
            }
        });


        Schema::dropIfExists('plans');
    }
};
