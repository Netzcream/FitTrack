<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercise_program_item_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_item_id')->constrained('exercise_program_items')->cascadeOnDelete();

            $table->unsignedBigInteger('student_id');
            $table->unsignedSmallInteger('set_index')->default(1); // 1..N
            $table->json('performed')->nullable(); // reps/tiempo/distancia/carga/RIR/tempo/observaciones
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['program_item_id', 'student_id', 'set_index']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('exercise_program_item_logs');
    }
};
