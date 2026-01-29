<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE conversations DROP INDEX conversations_type_tenant_id_index');
            DB::statement('ALTER TABLE conversations MODIFY tenant_id VARCHAR(64) NULL');
            DB::statement('ALTER TABLE conversations ADD INDEX conversations_type_tenant_id_index (type, tenant_id)');
        }
        // For SQLite and others, do nothing (or implement compatible logic if needed)
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE conversations DROP INDEX conversations_type_tenant_id_index');
            DB::statement('ALTER TABLE conversations MODIFY tenant_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE conversations ADD INDEX conversations_type_tenant_id_index (type, tenant_id)');
        }
        // For SQLite and others, do nothing
    }
};
