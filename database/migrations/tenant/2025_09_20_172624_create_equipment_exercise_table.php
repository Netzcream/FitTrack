<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('exercise_equipment_exercise', function (Blueprint $table) {
            $table->id();

            $table->foreignId('exercise_id')->constrained('exercise_exercises')->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('exercise_equipment')->cascadeOnDelete();

            $table->boolean('is_required')->default(true); // requerido para la variante base

            $table->timestamps();

            $table->unique(['exercise_id','equipment_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('exercise_equipment_exercise'); }
};
