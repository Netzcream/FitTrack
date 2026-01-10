<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade');
            $table->string('participant_type'); // central, tenant
            $table->unsignedBigInteger('participant_id');
            $table->timestamp('last_read_at')->nullable();
            $table->timestamp('muted_at')->nullable();

            $table->unique(['conversation_id', 'participant_type', 'participant_id'], 'conv_participant_unique');
            $table->index(['participant_type', 'participant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_participants');
    }
};
