<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('plan_exercise');
    }

    public function down(): void
    {
        Schema::create('plan_exercise', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->cascadeOnDelete();
            $table->integer('day')->default(1);
            $table->integer('order')->default(1);
            $table->string('detail', 50)->nullable();
            $table->string('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }
};
