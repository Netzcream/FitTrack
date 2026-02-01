<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('ALTER TABLE conversation_participants MODIFY participant_id VARCHAR(64) NULL');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('ALTER TABLE conversation_participants MODIFY participant_id BIGINT UNSIGNED NULL');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
