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
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');

            $table->string('name');
            $table->text('description')->nullable();

            $table->string('category')->nullable();
            $table->string('level')->nullable();      // beginner/intermediate/advanced
            $table->string('equipment')->nullable();  // barra, mancuernas, etc.

            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });

        // Add unique index for uuid if not exists
        $connection = Schema::getConnection();
        $result = $connection->selectOne(
            "SELECT COUNT(*) as count FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = 'exercises' AND index_name = 'exercises_uuid_unique'",
            [$connection->getDatabaseName()]
        );
        $indexExists = $result->count > 0;
        if (! $indexExists) {
            Schema::table('exercises', function (Blueprint $table) {
                $table->unique('uuid');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
