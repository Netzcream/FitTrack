<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_gamification_profiles', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();

            // Relación con estudiante (uno a uno)
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->unique('student_id');

            // XP y niveles
            $table->unsignedBigInteger('total_xp')->default(0)->comment('XP total acumulado (nunca decrece)');
            $table->unsignedInteger('current_level')->default(0)->comment('Nivel actual (derivado de total_xp)');
            $table->unsignedTinyInteger('current_tier')->default(0)->comment('Tier actual (0=Not Rated, 1-5=rangos)');

            // Badge activo (tier badge)
            $table->string('active_badge')->default('not_rated')->comment('Badge visual del tier actual');

            // Estadísticas complementarias (no punitivas)
            $table->unsignedInteger('total_exercises_completed')->default(0)->comment('Total de ejercicios únicos completados');
            $table->date('last_exercise_completed_at')->nullable()->comment('Última vez que completó un ejercicio');

            // Metadatos para futuras extensiones
            $table->json('meta')->nullable()->comment('Streaks, achievements, etc. (futuro)');

            $table->timestamps();

            // Índices para queries comunes
            $table->index('current_level');
            $table->index('current_tier');
            $table->index('total_xp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_gamification_profiles');
    }
};
