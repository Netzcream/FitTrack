<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_plan_assignments', function (Blueprint $table) {
            // Agregar campo status
            $table->string('status', 20)->default('active')->after('exercises_snapshot');
            $table->index('status');
        });

        // Migrar datos existentes basándose en is_active y fechas
        DB::statement("
            UPDATE student_plan_assignments
            SET status = CASE
                WHEN is_active = 1 AND starts_at <= CURDATE() THEN 'active'
                WHEN is_active = 0 AND starts_at > CURDATE() THEN 'pending'
                WHEN is_active = 0 AND ends_at < CURDATE() THEN 'completed'
                ELSE 'cancelled'
            END
        ");

        // Actualizar el constraint para usar status en lugar de is_active
        Schema::table('student_plan_assignments', function (Blueprint $table) {
            $table->dropUnique('uniq_active_assignment_per_student');
            $table->dropColumn('active_student_id');
        });

        Schema::table('student_plan_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('active_student_id')->nullable()
                ->storedAs("IF(status = 'active', student_id, NULL)")
                ->after('is_active');
            $table->unique('active_student_id', 'uniq_active_assignment_per_student');
        });
    }

    public function down(): void
    {
        Schema::table('student_plan_assignments', function (Blueprint $table) {
            $table->dropUnique('uniq_active_assignment_per_student');
            $table->dropColumn(['active_student_id', 'status']);
        });

        Schema::table('student_plan_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('active_student_id')->nullable()
                ->storedAs('IF(is_active, student_id, NULL)');
            $table->unique('active_student_id', 'uniq_active_assignment_per_student');
        });
    }
};
