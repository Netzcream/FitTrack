<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Verificar si ya se hizo la migración
        $columns = DB::select("SHOW COLUMNS FROM workouts WHERE Field = 'uuid'");
        if (!empty($columns)) {
            // Ya se migró, no hacer nada
            return;
        }

        // Paso 1: Obtener las foreign keys existentes y eliminarlas
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'workouts'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE workouts DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
        }

        // Paso 2: Eliminar todos los índices en la columna id
        $indexes = DB::select("
            SELECT DISTINCT INDEX_NAME
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'workouts'
            AND COLUMN_NAME = 'id'
            AND INDEX_NAME != 'PRIMARY'
        ");

        foreach ($indexes as $index) {
            DB::statement("ALTER TABLE workouts DROP INDEX {$index->INDEX_NAME}");
        }

        // Paso 3: Modificar id eliminando PRIMARY KEY en un solo paso y renombrarlo
        DB::statement('ALTER TABLE workouts MODIFY COLUMN id CHAR(36) NOT NULL');
        DB::statement('ALTER TABLE workouts DROP PRIMARY KEY');
        DB::statement('ALTER TABLE workouts CHANGE COLUMN id uuid_old CHAR(36) NOT NULL');

        // Paso 4: Agregar nuevo id autoincremental como primary key
        DB::statement('ALTER TABLE workouts ADD COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');

        // Paso 5: Agregar campo uuid
        DB::statement('ALTER TABLE workouts ADD COLUMN uuid CHAR(36) NOT NULL AFTER id');

        // Paso 6: Copiar uuid_old a uuid
        DB::statement('UPDATE workouts SET uuid = uuid_old');

        // Paso 7: Crear índice único en uuid
        DB::statement('ALTER TABLE workouts ADD UNIQUE KEY workouts_uuid_unique (uuid)');

        // Paso 8: Eliminar uuid_old
        DB::statement('ALTER TABLE workouts DROP COLUMN uuid_old');

        // Paso 9: Re-agregar las foreign keys
        Schema::table('workouts', function (Blueprint $table) {
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('student_plan_assignment_id')->references('id')->on('student_plan_assignments')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // Paso 1: Obtener y eliminar foreign keys
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'workouts'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE workouts DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
        }

        // Paso 2: Eliminar índice único de uuid
        DB::statement('ALTER TABLE workouts DROP INDEX workouts_uuid_unique');

        // Paso 3: Copiar uuid a columna temporal
        DB::statement('ALTER TABLE workouts ADD COLUMN id_temp CHAR(36) NOT NULL AFTER uuid');
        DB::statement('UPDATE workouts SET id_temp = uuid');

        // Paso 4: Eliminar columna uuid
        DB::statement('ALTER TABLE workouts DROP COLUMN uuid');

        // Paso 5: Eliminar primary key del id numérico
        DB::statement('ALTER TABLE workouts MODIFY COLUMN id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE workouts DROP PRIMARY KEY');
        DB::statement('ALTER TABLE workouts DROP COLUMN id');

        // Paso 6: Renombrar id_temp a id y convertirlo en primary key
        DB::statement('ALTER TABLE workouts CHANGE COLUMN id_temp id CHAR(36) NOT NULL');
        DB::statement('ALTER TABLE workouts ADD PRIMARY KEY (id)');

        // Paso 7: Re-agregar foreign keys
        Schema::table('workouts', function (Blueprint $table) {
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('student_plan_assignment_id')->references('id')->on('student_plan_assignments')->cascadeOnDelete();
        });
    }
};
