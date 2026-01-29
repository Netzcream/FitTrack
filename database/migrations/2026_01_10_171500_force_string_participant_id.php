<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            // Just alter the column; keep indexes to avoid FK/index drop issues
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::statement('ALTER TABLE conversation_participants MODIFY participant_id VARCHAR(64) NULL');
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
        // For SQLite and others, do nothing (or implement compatible logic if needed)
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::statement('ALTER TABLE conversation_participants MODIFY participant_id BIGINT UNSIGNED NULL');
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
        // For SQLite and others, do nothing
    }
};
