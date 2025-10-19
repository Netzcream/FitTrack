<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_plans', function (Blueprint $table) {
            // Nuevo campo relacional: plan asignado a un alumno
            $table->foreignId('student_id')
                ->nullable()
                ->constrained('students')
                ->nullOnDelete()
                ->after('is_active');

            // Asegurar que meta sea JSON y ampliar su uso
            $table->json('meta')->nullable()->change();
            $table->date('assigned_from')->nullable()->after('student_id');
            $table->date('assigned_until')->nullable()->after('assigned_from');

            // Índice opcional para filtrar por alumno
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::table('training_plans', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropIndex(['student_id']);
            $table->dropColumn(['student_id','assigned_from','assigned_until']);
        });
    }
};
