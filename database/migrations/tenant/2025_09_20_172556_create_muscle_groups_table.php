<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercise_muscle_groups', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->foreignId('parent_id')->nullable()->constrained('exercise_muscle_groups')->nullOnDelete();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        // Seed: exercise_muscle_groups
       /* $now = now();
        DB::table('exercise_muscle_groups')->insert([
            // Tronco superior
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Pecho',
                'code' => 'chest',
                'parent_id' => null,
                'description' => 'Grupo muscular del tórax (empuje horizontal/vertical).',
                'order' => 1,
                'status' => 'published',
                'meta' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Espalda',
                'code' => 'back',
                'parent_id' => null,
                'description' => 'Tracción horizontal/vertical, estabilización de columna.',
                'order' => 2,
                'status' => 'published',
                'meta' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Hombros',
                'code' => 'shoulders',
                'parent_id' => null,
                'description' => 'Deltoides (anterior, medio, posterior).',
                'order' => 3,
                'status' => 'published',
                'meta' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Brazos',
                'code' => 'arms',
                'parent_id' => null,
                'description' => 'Bíceps, tríceps y antebrazos.',
                'order' => 4,
                'status' => 'published',
                'meta' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Core
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Core',
                'code' => 'core',
                'parent_id' => null,
                'description' => 'Abdominales, oblicuos, transverso y erectores.',
                'order' => 5,
                'status' => 'published',
                'meta' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Tren inferior
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Glúteos',
                'code' => 'glutes',
                'parent_id' => null,
                'description' => 'Glúteo mayor, medio y menor.',
                'order' => 6,
                'status' => 'published',
                'meta' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Cuádriceps',
                'code' => 'quadriceps',
                'parent_id' => null,
                'description' => 'Recto femoral y vastos.',
                'order' => 7,
                'status' => 'published',
                'meta' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Isquiotibiales',
                'code' => 'hamstrings',
                'parent_id' => null,
                'description' => 'Semitendinoso, semimembranoso y bíceps femoral.',
                'order' => 8,
                'status' => 'published',
                'meta' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Pantorrillas',
                'code' => 'calves',
                'parent_id' => null,
                'description' => 'Gastrocnemio y sóleo.',
                'order' => 9,
                'status' => 'published',
                'meta' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Aductores',
                'code' => 'adductors',
                'parent_id' => null,
                'description' => 'Aductor mayor, largo, corto, pectíneo, gracilis.',
                'order' => 10,
                'status' => 'published',
                'meta' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Abductores / Cadera lateral',
                'code' => 'abductors',
                'parent_id' => null,
                'description' => 'TFL y estabilizadores laterales de cadera.',
                'order' => 11,
                'status' => 'published',
                'meta' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Cervical / escapular
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Trapecios',
                'code' => 'traps',
                'parent_id' => null,
                'description' => 'Porción superior, media e inferior del trapecio.',
                'order' => 12,
                'status' => 'published',
                'meta' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Antebrazos',
                'code' => 'forearms',
                'parent_id' => null,
                'description' => 'Flexores y extensores del antebrazo.',
                'order' => 13,
                'status' => 'published',
                'meta' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);*/
    }
    public function down(): void
    {
        Schema::dropIfExists('exercise_muscle_groups');
    }
};
