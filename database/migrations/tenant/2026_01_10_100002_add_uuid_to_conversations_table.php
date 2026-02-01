<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only add uuid column if it doesn't exist
        if (!Schema::hasColumn('conversations', 'uuid')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->after('id');
            });
        }

        // Generate UUIDs for existing conversations
        DB::table('conversations')->whereNull('uuid')->cursor()->each(function ($conversation) {
            DB::table('conversations')
                ->where('id', $conversation->id)
                ->update(['uuid' => Str::uuid()->toString()]);
        });

        // Make uuid unique and not nullable, but only if the index does not already exist
        $connection = Schema::getConnection();
        $result = $connection->selectOne(
            "SELECT COUNT(*) as count FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = 'conversations' AND index_name = 'conversations_uuid_unique'",
            [$connection->getDatabaseName()]
        );
        $indexExists = $result->count > 0;

        if (!$indexExists) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->uuid('uuid')->nullable(false)->unique()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
