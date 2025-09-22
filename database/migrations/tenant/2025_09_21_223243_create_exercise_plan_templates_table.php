<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercise_plan_templates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->unsignedInteger('version')->default(1);
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['status', 'version']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('exercise_plan_templates');
    }
};
