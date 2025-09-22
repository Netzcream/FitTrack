<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('exercise_exercise_muscle', function (Blueprint $table) {
            $table->id();

            $table->foreignId('exercise_id')->constrained('exercise_exercises')->cascadeOnDelete();
            $table->foreignId('muscle_id')->constrained('exercise_muscles')->cascadeOnDelete();

            $table->string('role')->default('primary'); // primary | secondary | stabilizer (texto, extensible)
            $table->unsignedTinyInteger('involvement_pct')->nullable(); // 0–100

            $table->timestamps();

            $table->unique(['exercise_id','muscle_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('exercise_exercise_muscle'); }
};
