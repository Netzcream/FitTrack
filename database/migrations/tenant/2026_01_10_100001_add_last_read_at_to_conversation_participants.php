<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $hasLastReadAt = Schema::hasColumn('conversation_participants', 'last_read_at');
        $hasMutedAt = Schema::hasColumn('conversation_participants', 'muted_at');

        Schema::table('conversation_participants', function (Blueprint $table) use ($hasLastReadAt, $hasMutedAt) {
            if (!$hasLastReadAt) {
                $table->timestamp('last_read_at')->nullable()->after('participant_id');
            }
            if (!$hasMutedAt) {
                $table->timestamp('muted_at')->nullable()->after('participant_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversation_participants', function (Blueprint $table) {
            $table->dropColumn(['last_read_at', 'muted_at']);
        });
    }
};
