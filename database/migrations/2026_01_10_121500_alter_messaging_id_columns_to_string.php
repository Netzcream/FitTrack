<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Central tables: ensure string-based IDs for tenant slugs
        if (Schema::hasTable('conversations')) {
            Schema::table('conversations', function (Blueprint $table) {
                if (Schema::hasColumn('conversations', 'tenant_id')) {
                    $table->string('tenant_id', 64)->change();
                }
            });
        }

        if (Schema::hasTable('conversation_participants')) {
            Schema::table('conversation_participants', function (Blueprint $table) {
                if (Schema::hasColumn('conversation_participants', 'participant_id')) {
                    $table->string('participant_id', 64)->change();
                    $table->index(['participant_type', 'participant_id'], 'participant_type_id_idx');
                }
            });
        }

        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                if (Schema::hasColumn('messages', 'sender_id')) {
                    $table->string('sender_id', 64)->change();
                }
            });
        }
    }

    public function down(): void
    {
        // Revert to unsignedBigInteger where applicable
        if (Schema::hasTable('conversations')) {
            Schema::table('conversations', function (Blueprint $table) {
                if (Schema::hasColumn('conversations', 'tenant_id')) {
                    $table->unsignedBigInteger('tenant_id')->change();
                }
            });
        }

        if (Schema::hasTable('conversation_participants')) {
            Schema::table('conversation_participants', function (Blueprint $table) {
                if (Schema::hasColumn('conversation_participants', 'participant_id')) {
                    $table->unsignedBigInteger('participant_id')->change();
                    $table->dropIndex('participant_type_id_idx');
                }
            });
        }

        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                if (Schema::hasColumn('messages', 'sender_id')) {
                    $table->unsignedBigInteger('sender_id')->change();
                }
            });
        }
    }
};
