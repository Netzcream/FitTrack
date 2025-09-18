<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_training_goal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('training_goal_id')->constrained('training_goals')->cascadeOnDelete();
            $table->string('role', 20)->default('secondary'); // primary|secondary (por si después unificás)
            $table->timestamps();

            $table->unique(['student_id', 'training_goal_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_training_goal');
    }
};
