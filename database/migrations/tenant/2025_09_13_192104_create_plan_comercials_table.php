<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('commercial_plans', function (Blueprint $table) {
            $table->id();

            // identidad del plan
            $table->string('name');              // e.g., Starter, Pro, Enterprise
            $table->string('uuid')->unique();
            $table->string('code')->unique();    // clave interna p.e. PRO, ENT (distinto del slug)
            $table->string('slug')->unique();    // url-friendly if needed
            $table->text('description')->nullable();

            // precios y facturación
            $table->decimal('monthly_price', 10, 2)->nullable();
            $table->decimal('yearly_price', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD'); // ISO-4217

            $table->enum('billing_interval', ['monthly', 'yearly', 'both'])->default('both');
            $table->unsignedSmallInteger('trial_days')->default(0);

            // límites/cupos
            $table->unsignedInteger('max_users')->nullable();      // null = ilimitado
            $table->unsignedInteger('max_teams')->nullable();
            $table->unsignedInteger('max_projects')->nullable();
            $table->unsignedInteger('storage_gb')->nullable();

            // toggles del plan
            $table->boolean('is_active')->default(true);
            $table->enum('visibility', ['public', 'private'])->default('public'); // en el selector de pricing
            $table->enum('plan_type', ['free', 'standard', 'pro', 'enterprise'])->default('standard');

            // catálogos / integraciones
            $table->json('features')->nullable();   // listado de features para mostrar
            $table->json('limits')->nullable();     // estructura de límites por módulo
            $table->string('external_product_id')->nullable(); // Stripe product
            $table->string('external_monthly_price_id')->nullable(); // Stripe price (mensual)
            $table->string('external_yearly_price_id')->nullable();  // Stripe price (anual)

            $table->unsignedInteger('sort_order')->default(0);

            $table->softDeletes();
            $table->timestamps();

            // índices habituales de filtro/ordenamiento
            $table->index(['is_active', 'visibility', 'plan_type']);
            $table->index(['sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commercial_plans');
    }
};
