<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();

            $table->morphs('model');
            $table->uuid('uuid')->nullable();
            $table->string('collection_name');
            $table->string('name');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->string('disk');
            $table->string('conversions_disk')->nullable();
            $table->unsignedBigInteger('size');
            $table->json('manipulations');
            $table->json('custom_properties');
            $table->json('generated_conversions');
            $table->json('responsive_images');
            $table->unsignedInteger('order_column')->nullable()->index();

            $table->nullableTimestamps();
        });

        // Add unique index for uuid if not exists
        $connection = Schema::getConnection();
        $result = $connection->selectOne(
            "SELECT COUNT(*) as count FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = 'media' AND index_name = 'media_uuid_unique'",
            [$connection->getDatabaseName()]
        );
        $indexExists = $result->count > 0;
        if (! $indexExists) {
            Schema::table('media', function (Blueprint $table) {
                $table->unique('uuid');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
