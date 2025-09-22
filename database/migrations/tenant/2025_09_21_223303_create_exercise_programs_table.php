<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercise_programs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->enum('status', ['active', 'paused', 'completed', 'archived'])->default('active');

            // Trazabilidad a la plantilla (opcional, se copia contenido)
            $table->foreignId('template_id')->nullable()->constrained('exercise_plan_templates')->nullOnDelete();
            $table->unsignedInteger('template_version')->nullable();
            $table->json('origin_info')->nullable(); // metadatos al instanciar

            $table->date('start_date')->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['status', 'start_date']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('exercise_programs');
    }
};
