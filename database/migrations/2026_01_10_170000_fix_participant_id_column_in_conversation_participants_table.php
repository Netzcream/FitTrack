<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = Schema::getConnection();
        $tableName = 'conversation_participants';
        $databaseName = $connection->getDatabaseName();

        // Helper to check if index exists
        $indexExists = function ($indexName) use ($connection, $tableName, $databaseName) {
            $result = $connection->selectOne(
                "SELECT COUNT(*) as count FROM information_schema.statistics
                 WHERE table_schema = ? AND table_name = ? AND index_name = ?",
                [$databaseName, $tableName, $indexName]
            );
            return $result->count > 0;
        };

        // Add dedicated index for conversation_id if it doesn't exist
        if (!$indexExists('conversation_participants_conversation_id_index')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->index('conversation_id', 'conversation_participants_conversation_id_index');
            });
        }

        // Drop indexes before altering, only if they exist
        $indexesToDrop = [
            'conv_participant_unique',
            'participant_type_id_idx',
            'cpt_pt_pid_idx',
            'conv_participant_type_id_index',
            'conversation_participants_participant_type_participant_id_index'
        ];

        foreach ($indexesToDrop as $indexName) {
            if ($indexExists($indexName)) {
                Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                    if (str_contains($indexName, 'unique')) {
                        $table->dropUnique($indexName);
                    } else {
                        $table->dropIndex($indexName);
                    }
                });
            }
        }

        Schema::table($tableName, function (Blueprint $table) {
            // Change to string(255) to match tenant id (slug)
            $table->string('participant_id', 255)->change();

            // Recreate indexes with explicit names
            $table->unique(['conversation_id', 'participant_type', 'participant_id'], 'conv_participant_unique');
            $table->index(['participant_type', 'participant_id'], 'conv_participant_type_id_index');
        });
    }

    public function down(): void
    {
        $connection = Schema::getConnection();
        $tableName = 'conversation_participants';
        $databaseName = $connection->getDatabaseName();

        // Helper to check if index exists
        $indexExists = function ($indexName) use ($connection, $tableName, $databaseName) {
            $result = $connection->selectOne(
                "SELECT COUNT(*) as count FROM information_schema.statistics
                 WHERE table_schema = ? AND table_name = ? AND index_name = ?",
                [$databaseName, $tableName, $indexName]
            );
            return $result->count > 0;
        };

        // Drop indexes created in up()
        $indexesToDrop = [
            'conv_participant_unique',
            'conv_participant_type_id_index',
            'participant_type_id_idx',
            'cpt_pt_pid_idx',
            'conversation_participants_participant_type_participant_id_index',
            'conversation_participants_conversation_id_index'
        ];

        foreach ($indexesToDrop as $indexName) {
            if ($indexExists($indexName)) {
                Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                    if (str_contains($indexName, 'unique')) {
                        $table->dropUnique($indexName);
                    } else {
                        $table->dropIndex($indexName);
                    }
                });
            }
        }

        Schema::table($tableName, function (Blueprint $table) {
            $table->unsignedBigInteger('participant_id')->change();
            $table->unique(['conversation_id', 'participant_type', 'participant_id'], 'conv_participant_unique');
            $table->index(['participant_type', 'participant_id'], 'conv_participant_type_id_index');
        });
    }
};
