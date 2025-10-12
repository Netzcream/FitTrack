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
        Schema::create('plan_exercise', function (Blueprint $table) {
            $table->id();

            $table->foreignId('training_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('day')->nullable();
            $table->unsignedInteger('order')->nullable();
            $table->string('detail')->nullable();  // libre: "10 12 15 12 10" o "5 min x 4"
            $table->text('notes')->nullable();     // observaciones del entrenador
            $table->json('meta')->nullable();      // tempo, RPE, variaciones

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_exercise');
    }
};
