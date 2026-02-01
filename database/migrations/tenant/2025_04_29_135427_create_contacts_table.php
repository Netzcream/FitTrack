<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid'); // Para multi-tenant
            $table->string('name');
            $table->string('email');
            $table->string('mobile');
            $table->text('message');
            $table->softDeletes();
            $table->timestamps();
        });

        // Add unique index for uuid if not exists
        $connection = Schema::getConnection();
        $result = $connection->selectOne(
            "SELECT COUNT(*) as count FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = 'contacts' AND index_name = 'contacts_uuid_unique'",
            [$connection->getDatabaseName()]
        );
        $indexExists = $result->count > 0;
        if (! $indexExists) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->unique('uuid');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
