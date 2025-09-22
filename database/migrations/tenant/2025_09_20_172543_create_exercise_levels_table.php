<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercise_levels', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code')->unique();
            $table->string('name')->unique();   // ej: beginner, intermediate, advanced
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->json('meta')->nullable();
            $table->timestamps();
        });


       /* $now = now();
        DB::table('exercise_levels')->insert([
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Principiante',
                'code' => 'beginner',
                'description' => 'Ejercicios básicos para quienes inician su camino fitness.',
                'order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Intermedio',
                'code' => 'intermediate',
                'description' => 'Ejercicios para quienes ya tienen experiencia y buscan un reto mayor.',
                'order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Avanzado',
                'code' => 'advanced',
                'description' => 'Ejercicios desafiantes para atletas experimentados.',
                'order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);*/
    }
    public function down(): void
    {
        Schema::dropIfExists('exercise_levels');
    }
};
