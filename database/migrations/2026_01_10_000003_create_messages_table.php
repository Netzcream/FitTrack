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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade');
            $table->string('sender_type'); // central, tenant
            $table->unsignedBigInteger('sender_id');
            $table->text('body');
            $table->json('attachments')->nullable();
            $table->string('status')->default('sent'); // sent, delivered, read
            $table->timestamps();
            $table->softDeletes();

            $table->index('conversation_id');
            $table->index(['sender_type', 'sender_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
