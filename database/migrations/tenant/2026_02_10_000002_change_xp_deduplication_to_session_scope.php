<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const TABLE = 'exercise_completion_logs';
    private const LEGACY_UNIQUE_INDEX = 'unique_student_exercise_per_day';
    private const SESSION_UNIQUE_INDEX = 'exercise_completion_logs_student_session_exercise_unique';
    private const SESSION_INDEX = 'exercise_completion_logs_student_session_index';

    public function up(): void
    {
        if (!Schema::hasColumn(self::TABLE, 'session_instance_id')) {
            Schema::table(self::TABLE, function (Blueprint $table) {
                $table->uuid('session_instance_id')
                    ->nullable()
                    ->after('workout_id')
                    ->comment('Run ID de la sesión para idempotencia de XP por ejercicio');
            });
        }

        // Backfill preferente desde workouts.session_instance_id / workouts.uuid.
        DB::statement(
            'UPDATE exercise_completion_logs e
             LEFT JOIN workouts w ON w.id = e.workout_id
             SET e.session_instance_id = COALESCE(e.session_instance_id, w.session_instance_id, w.uuid)
             WHERE e.session_instance_id IS NULL'
        );

        // Fallback para logs legacy sin workout asociado.
        DB::table(self::TABLE)
            ->whereNull('session_instance_id')
            ->orderBy('id')
            ->chunkById(500, function ($logs): void {
                foreach ($logs as $log) {
                    DB::table(self::TABLE)
                        ->where('id', $log->id)
                        ->update([
                            'session_instance_id' => (string) Str::orderedUuid(),
                        ]);
                }
            });

        if ($this->indexExists(self::TABLE, self::LEGACY_UNIQUE_INDEX)) {
            DB::statement('ALTER TABLE ' . self::TABLE . ' DROP INDEX ' . self::LEGACY_UNIQUE_INDEX);
        }

        // Forzamos NOT NULL para que el unique funcione en todos los inserts.
        DB::statement('ALTER TABLE ' . self::TABLE . ' MODIFY session_instance_id CHAR(36) NOT NULL');

        if (!$this->indexExists(self::TABLE, self::SESSION_UNIQUE_INDEX)) {
            DB::statement(
                'ALTER TABLE ' . self::TABLE .
                ' ADD UNIQUE KEY ' . self::SESSION_UNIQUE_INDEX . ' (student_id, session_instance_id, exercise_id)'
            );
        }

        if (!$this->indexExists(self::TABLE, self::SESSION_INDEX)) {
            DB::statement(
                'ALTER TABLE ' . self::TABLE .
                ' ADD INDEX ' . self::SESSION_INDEX . ' (student_id, session_instance_id)'
            );
        }
    }

    public function down(): void
    {
        if ($this->indexExists(self::TABLE, self::SESSION_UNIQUE_INDEX)) {
            DB::statement('ALTER TABLE ' . self::TABLE . ' DROP INDEX ' . self::SESSION_UNIQUE_INDEX);
        }

        if ($this->indexExists(self::TABLE, self::SESSION_INDEX)) {
            DB::statement('ALTER TABLE ' . self::TABLE . ' DROP INDEX ' . self::SESSION_INDEX);
        }

        if (Schema::hasColumn(self::TABLE, 'session_instance_id')) {
            Schema::table(self::TABLE, function (Blueprint $table) {
                $table->dropColumn('session_instance_id');
            });
        }

        if (!$this->indexExists(self::TABLE, self::LEGACY_UNIQUE_INDEX)) {
            try {
                DB::statement(
                    'ALTER TABLE ' . self::TABLE .
                    ' ADD UNIQUE KEY ' . self::LEGACY_UNIQUE_INDEX . ' (student_id, exercise_id, completed_date)'
                );
            } catch (\Throwable $exception) {
                // Best effort: puede fallar si existen duplicados por día creados con la nueva regla por sesión.
            }
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select(
            'SELECT 1
             FROM information_schema.statistics
             WHERE table_schema = database()
               AND table_name = ?
               AND index_name = ?
             LIMIT 1',
            [$table, $indexName]
        );

        return !empty($result);
    }
};

