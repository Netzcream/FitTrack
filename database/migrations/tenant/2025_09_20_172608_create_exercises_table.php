<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercise_exercises', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name')->unique();
            $table->string('code')->unique();

            // Enums (valores cerrados con comportamiento)
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->enum('default_modality', [
                'reps',
                'time',
                'distance',
                'calories',
                'rpe',
                'load_only',
                'tempo_only',
            ])->default('reps');

            // Catálogos abiertos
            $table->foreignId('exercise_level_id')->nullable()->constrained('exercise_levels')->nullOnDelete();
            $table->foreignId('movement_pattern_id')->nullable()->constrained('exercise_movement_patterns')->nullOnDelete();
            $table->foreignId('exercise_plane_id')->nullable()->constrained('exercise_planes')->nullOnDelete();

            // Atributos
            $table->boolean('unilateral')->default(false);
            $table->boolean('external_load')->default(false);

            // Prescripción base (plantilla)
            $table->json('default_prescription')->nullable();
            $table->string('tempo_notation')->nullable();

            // Notas y guías
            $table->text('range_of_motion_notes')->nullable();
            $table->text('equipment_notes')->nullable();

            // Arrays de tips/cues
            $table->json('setup_steps')->nullable();
            $table->json('execution_cues')->nullable();
            $table->json('common_mistakes')->nullable();

            $table->text('breathing')->nullable();
            $table->text('safety_notes')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices útiles
            $table->index('status', 'ex_status_idx');
            $table->index('exercise_level_id', 'ex_lvl_idx');
            $table->index('movement_pattern_id', 'ex_pat_idx');
            $table->index('exercise_plane_id', 'ex_plane_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_exercises');
    }
};
