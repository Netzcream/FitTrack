<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_weight_entries', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();

            // Weight data
            $table->decimal('weight_kg', 5, 2);
            $table->string('source')->default('manual')->comment('manual, scale_device, api');

            // When recorded
            $table->dateTime('recorded_at');

            // Optional fields
            $table->text('notes')->nullable();
            $table->json('meta')->nullable()->comment('Optional metadata');

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index(['student_id', 'recorded_at']);
            $table->index('recorded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_weight_entries');
    }
};
