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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('type'); // tenant_student
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('subject')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'student_id']);
            $table->index('last_message_at');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });

        // Add unique index for uuid if not exists
        $result = DB::selectOne(
            "SELECT 1 FROM information_schema.statistics WHERE table_schema = database() AND table_name = 'conversations' AND index_name = 'conversations_uuid_unique' LIMIT 1"
        );
        $indexExists = $result !== null;
        if (! $indexExists) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->unique('uuid');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
