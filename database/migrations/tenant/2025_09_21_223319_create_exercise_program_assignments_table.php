<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercise_program_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('exercise_programs')->cascadeOnDelete();

            // Polimórfico por si en el futuro asignás a Team, etc.
            $table->string('assignable_type'); // 'students' model FQCN u otro
            $table->unsignedBigInteger('assignable_id');

            $table->foreignId('coach_id')->nullable()->constrained('users')->nullOnDelete();

            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->enum('status', ['assigned', 'in_progress', 'completed', 'cancelled'])->default('assigned');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['assignable_type', 'assignable_id']);
            $table->index(['program_id', 'status']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('exercise_program_assignments');
    }
};
