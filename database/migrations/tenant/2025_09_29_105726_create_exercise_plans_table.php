<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercise_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_template_id')->nullable()->constrained('exercise_plan_templates')->nullOnDelete();

            $table->string('name')->default('');
            $table->string('code')->unique();
            $table->enum('status', ['draft','active','paused','completed','archived'])->default('draft');

            $table->string('phase')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->text('notes')->nullable();
            $table->text('public_notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->string('source_template_version')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_plans');
    }
};
