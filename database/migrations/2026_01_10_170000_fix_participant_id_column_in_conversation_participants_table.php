<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure a dedicated index exists for the foreign key on conversation_id
        // MySQL requires an index for FK constraints; currently the FK may be using
        // the composite unique index 'conv_participant_unique'. Add an explicit
        // index so we can safely drop/recreate the composite index.
        Schema::table('conversation_participants', function (Blueprint $table) {
            $table->index('conversation_id', 'conversation_participants_conversation_id_index');
        });

        Schema::table('conversation_participants', function (Blueprint $table) {
            // Drop indexes before altering
            $table->dropUnique('conv_participant_unique');
            $table->dropIndex(['participant_type', 'participant_id']);
        });

        Schema::table('conversation_participants', function (Blueprint $table) {
            // Change to string to match tenant id (slug)
            $table->string('participant_id')->change();

            // Recreate indexes with explicit names
            $table->unique(['conversation_id', 'participant_type', 'participant_id'], 'conv_participant_unique');
            $table->index(['participant_type', 'participant_id'], 'conv_participant_type_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('conversation_participants', function (Blueprint $table) {
            $table->dropUnique('conv_participant_unique');
            $table->dropIndex('conv_participant_type_id_index');
            // Drop the explicit conversation_id index added in up()
            $table->dropIndex('conversation_participants_conversation_id_index');
        });

        Schema::table('conversation_participants', function (Blueprint $table) {
            $table->unsignedBigInteger('participant_id')->change();
            $table->unique(['conversation_id', 'participant_type', 'participant_id'], 'conv_participant_unique');
            $table->index(['participant_type', 'participant_id'], 'conv_participant_type_id_index');
        });
    }
};
