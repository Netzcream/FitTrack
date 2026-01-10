<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE conversations DROP INDEX conversations_type_tenant_id_index');
        DB::statement('ALTER TABLE conversations MODIFY tenant_id VARCHAR(64) NULL');
        DB::statement('ALTER TABLE conversations ADD INDEX conversations_type_tenant_id_index (type, tenant_id)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE conversations DROP INDEX conversations_type_tenant_id_index');
        DB::statement('ALTER TABLE conversations MODIFY tenant_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE conversations ADD INDEX conversations_type_tenant_id_index (type, tenant_id)');
    }
};
