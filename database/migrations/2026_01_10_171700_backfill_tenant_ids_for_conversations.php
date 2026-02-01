<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<SQL
UPDATE conversations c
JOIN conversation_participants cp
  ON cp.conversation_id = c.id
 AND cp.participant_type = 'tenant'
SET c.tenant_id = cp.participant_id
WHERE c.type = 'central_tenant'
  AND (c.tenant_id IS NULL OR c.tenant_id = '' OR c.tenant_id REGEXP '^[0-9]+$');
SQL);
    }

    public function down(): void
    {
        // No-op; leave data as is
    }
};
