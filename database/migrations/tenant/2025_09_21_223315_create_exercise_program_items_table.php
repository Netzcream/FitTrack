<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercise_program_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_block_id')->constrained('exercise_program_blocks')->cascadeOnDelete();

            $table->foreignId('template_item_id')->nullable()->constrained('exercise_plan_template_items')->nullOnDelete();

            $table->foreignId('exercise_id')->nullable()->constrained('exercise_exercises')->nullOnDelete();
            $table->string('display_name')->nullable();       // snapshot opcional
            $table->json('snapshot_exercise')->nullable();    // snapshot opcional

            $table->unsignedInteger('order')->default(0);

            // receta efectiva para el alumno (puede diferir de la plantilla)
            $table->json('prescription')->nullable();

            // overrides
            $table->string('tempo')->nullable();
            $table->unsignedSmallInteger('rest_seconds')->nullable();
            $table->unsignedTinyInteger('rpe')->nullable();
            $table->boolean('external_load')->default(false);

            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['program_block_id', 'order'], 'prg_item_ord_idx');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('exercise_program_items');
    }
};
