<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('students');
        Schema::enableForeignKeyConstraints();

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Identificación principal
            $table->string('status', 20)->default('active'); // active|paused|inactive|prospect
            $table->string('email')->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('phone', 30)->nullable();
            $table->string('timezone', 64)->nullable();
            $table->string('goal', 255)->nullable();

            // Acceso / cuenta
            $table->boolean('is_user_enabled')->default(true);
            $table->timestamp('last_login_at')->nullable();

            // Plan comercial
            $table->foreignId('commercial_plan_id')->nullable()->constrained('commercial_plans')->nullOnDelete();
            $table->string('billing_frequency', 20)->default('monthly'); // monthly|quarterly|yearly
            $table->string('account_status', 30)->default('on_time');    // on_time|due|review

            // Datos extra unificados
            $table->json('data')->nullable(); // Ej: birth_date, gender, metrics, preferencias

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('students');
        Schema::enableForeignKeyConstraints();

        // Restaura la estructura previa (versión extendida con múltiples JSON)
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Identificación
            $table->string('status', 20)->default('active'); // active|paused|inactive|prospect
            $table->string('email')->index();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('goal')->nullable();

            // Contacto general
            $table->string('phone')->nullable();
            $table->string('timezone', 64)->nullable();

            // Acceso y perfil
            $table->boolean('is_user_enabled')->default(false);
            $table->timestamp('last_login_at')->nullable();

            // Nivel actual (usado en filtros)
            $table->string('current_level', 20)->nullable();

            // Facturación visible (reportes)
            $table->foreignId('commercial_plan_id')->nullable()->constrained('commercial_plans')->nullOnDelete();
            $table->string('billing_frequency', 20)->nullable(); // monthly|quarterly|yearly
            $table->string('account_status', 30)->nullable();    // on_time|due|review

            /* --------------------------- Agrupaciones JSON --------------------------- */

            $table->json('personal_data')->nullable();       // { birth_date, gender, height_cm, weight_kg }
            $table->json('health_data')->nullable();         // { injuries, allergies, apt_fitness, etc. }
            $table->json('training_data')->nullable();       // { preferences, sessions, adherence, etc. }
            $table->json('communication_data')->nullable();  // { language, notifications }
            $table->json('extra_data')->nullable();          // { emergency_contact, links, notes }

            $table->timestamps();
            $table->softDeletes();
        });
    }
};
