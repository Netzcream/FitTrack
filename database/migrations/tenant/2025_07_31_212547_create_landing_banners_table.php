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
        Schema::create('landing_banners', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');

            $table->string('text')->nullable();
            $table->string('link')->nullable();
            $table->string('target')->default('_self');
            $table->boolean('active')->default(1);
            $table->unsignedInteger('order')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        // Add unique index for uuid if not exists
        $connection = Schema::getConnection();
        $result = $connection->selectOne(
            "SELECT COUNT(*) as count FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = 'landing_banners' AND index_name = 'landing_banners_uuid_unique'",
            [$connection->getDatabaseName()]
        );
        $indexExists = $result->count > 0;
        if (! $indexExists) {
            Schema::table('landing_banners', function (Blueprint $table) {
                $table->unique('uuid');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_banners');
    }
};
