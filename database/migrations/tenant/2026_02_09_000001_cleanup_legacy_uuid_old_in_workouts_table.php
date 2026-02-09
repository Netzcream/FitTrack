<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('workouts')) {
            return;
        }

        if (Schema::hasColumn('workouts', 'uuid') && !$this->hasUuidUniqueIndex()) {
            DB::statement('ALTER TABLE workouts ADD UNIQUE KEY workouts_uuid_unique (uuid)');
        }

        if (!Schema::hasColumn('workouts', 'uuid_old')) {
            return;
        }

        DB::statement("
            UPDATE workouts
            SET uuid_old = uuid
            WHERE (uuid_old IS NULL OR uuid_old = '')
              AND uuid IS NOT NULL
        ");

        DB::statement('ALTER TABLE workouts DROP COLUMN uuid_old');
    }

    public function down(): void
    {
        if (!Schema::hasTable('workouts')) {
            return;
        }

        if (!Schema::hasColumn('workouts', 'uuid_old') && Schema::hasColumn('workouts', 'uuid')) {
            Schema::table('workouts', function (Blueprint $table) {
                $table->uuid('uuid_old')->nullable()->after('uuid');
            });

            DB::statement('UPDATE workouts SET uuid_old = uuid WHERE uuid_old IS NULL');
        }
    }

    private function hasUuidUniqueIndex(): bool
    {
        $indexes = DB::select("
            SELECT 1
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'workouts'
              AND INDEX_NAME = 'workouts_uuid_unique'
            LIMIT 1
        ");

        return !empty($indexes);
    }
};
