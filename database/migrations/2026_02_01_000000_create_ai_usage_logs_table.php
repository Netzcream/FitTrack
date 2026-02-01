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
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->string('month', 7)->index(); // '2026-02'
            $table->unsignedInteger('usage_count')->default(0);
            $table->unsignedInteger('limit')->default(0);
            $table->string('plan_slug', 50)->nullable();
            $table->json('meta')->nullable(); // Para datos adicionales futuros
            $table->timestamps();

            // Índice compuesto para búsquedas eficientes
            $table->unique(['tenant_id', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
    }
};
