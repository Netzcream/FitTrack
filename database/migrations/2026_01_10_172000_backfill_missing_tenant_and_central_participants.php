<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Backfill missing TENANT participants for central_tenant conversations
        DB::statement(<<<SQL
INSERT INTO conversation_participants (conversation_id, participant_type, participant_id, last_read_at, muted_at)
SELECT c.id, 'tenant', c.tenant_id, NULL, NULL
FROM conversations c
LEFT JOIN conversation_participants cp
  ON cp.conversation_id = c.id
 AND cp.participant_type = 'tenant'
WHERE c.type = 'central_tenant'
  AND cp.id IS NULL
  AND c.tenant_id IS NOT NULL
  AND c.tenant_id <> '';
SQL);

        // Backfill missing CENTRAL participants for central_tenant conversations
        DB::statement(<<<SQL
INSERT INTO conversation_participants (conversation_id, participant_type, participant_id, last_read_at, muted_at)
SELECT c.id, 'central', 1, NULL, NULL
FROM conversations c
LEFT JOIN conversation_participants cp
  ON cp.conversation_id = c.id
 AND cp.participant_type = 'central'
WHERE c.type = 'central_tenant'
  AND cp.id IS NULL;
SQL);
    }

    public function down(): void
    {
        // Remove backfilled rows (only those without last_read_at/muted_at, to avoid deleting legitimate entries)
        DB::statement(<<<SQL
DELETE cp FROM conversation_participants cp
JOIN conversations c
  ON c.id = cp.conversation_id
WHERE c.type = 'central_tenant'
  AND ((cp.participant_type = 'tenant' AND (cp.last_read_at IS NULL AND cp.muted_at IS NULL))
    OR (cp.participant_type = 'central' AND (cp.last_read_at IS NULL AND cp.muted_at IS NULL)));
SQL);
    }
};
