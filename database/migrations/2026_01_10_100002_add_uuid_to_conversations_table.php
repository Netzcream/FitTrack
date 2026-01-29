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

        // Make uuid unique and not nullable (guard against duplicate index creation)
        $driver = Schema::getConnection()->getDriverName();
        $indexExists = false;

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('conversations')");
            foreach ($indexes as $index) {
                if (($index->name ?? null) === 'conversations_uuid_unique') {
                    $indexExists = true;
                    break;
                }
            }
        } elseif ($driver === 'mysql') {
            $result = DB::selectOne(
                "SELECT 1 FROM information_schema.statistics WHERE table_schema = database() AND table_name = 'conversations' AND index_name = 'conversations_uuid_unique' LIMIT 1"
            );
            $indexExists = $result !== null;
        } elseif ($driver === 'pgsql') {
            $result = DB::selectOne(
                "SELECT 1 FROM pg_indexes WHERE tablename = 'conversations' AND indexname = 'conversations_uuid_unique' LIMIT 1"
            );
            $indexExists = $result !== null;
        }

        if (
            Schema::hasColumn('conversations', 'uuid') &&
            DB::table('conversations')->whereNull('uuid')->exists() === false &&
            $indexExists === false
        ) {
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
