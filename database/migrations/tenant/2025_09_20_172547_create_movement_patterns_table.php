<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercise_movement_patterns', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code')->unique();
            $table->string('name')->unique();   // squat, hinge, lunge, ...
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        /*$now = now();
        DB::table('exercise_movement_patterns')->insert([
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Sentadilla',
                'code' => 'squat',
                'description' => 'Patrón básico de flexión y extensión de rodillas/cadera.',
                'order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Bisagra de cadera',
                'code' => 'hinge',
                'description' => 'Patrón dominante de cadera (peso muerto, hip thrust).',
                'order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Zancada',
                'code' => 'lunge',
                'description' => 'Patrón unilateral de pierna (lunge, split squat).',
                'order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Empuje',
                'code' => 'push',
                'description' => 'Patrón de empuje (horizontal/vertical).',
                'order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Tracción',
                'code' => 'pull',
                'description' => 'Patrón de tracción (horizontal/vertical).',
                'order' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Porteo / Levante',
                'code' => 'carry',
                'description' => 'Patrón de carga y transporte (farmer walk, suitcase carry).',
                'order' => 6,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Rotación',
                'code' => 'rotation',
                'description' => 'Patrón rotacional del core (twists, lanzamientos).',
                'order' => 7,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Antirotación',
                'code' => 'anti_rotation',
                'description' => 'Patrón de resistencia al movimiento rotacional (Pallof press).',
                'order' => 8,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);*/
    }
    public function down(): void
    {
        Schema::dropIfExists('exercise_movement_patterns');
    }
};
