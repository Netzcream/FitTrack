<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('workouts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Relaciones
            $table->foreignId('student_id')
                ->constrained('students')
                ->onDelete('cascade');

            $table->foreignId('training_plan_id')
                ->nullable()
                ->constrained('training_plans')
                ->onDelete('set null');

            // Datos de la sesión
            $table->date('date');
            $table->integer('duration_minutes')->nullable();
            $table->string('status')->default('completed'); // completed, in_progress, skipped
            $table->text('notes')->nullable();
            $table->tinyInteger('rating')->nullable(); // 1-5 estrellas

            // Metadata flexible
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['student_id', 'date']);
            $table->index(['training_plan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workouts');
    }
};
