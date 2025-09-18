<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Identificación
            $table->string('status', 20)->default('active'); // active|paused|inactive|prospect

            // Datos personales
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('document_number')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender', 20)->nullable(); // male|female|non_binary|other
            $table->decimal('height_cm', 6, 2)->nullable();
            $table->decimal('weight_kg', 6, 2)->nullable();

            // Contacto
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();
            $table->string('timezone', 64)->nullable();

            // Acceso y perfil
            $table->boolean('is_user_enabled')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->string('language', 5)->nullable(); // es|en
            $table->string('avatar_path')->nullable();

            // Objetivos y preferencias
            $table->foreignId('primary_training_goal_id')->nullable()->constrained('training_goals')->nullOnDelete();
            $table->json('secondary_goals')->nullable();        // ids o textos
            $table->text('availability_text')->nullable();
            $table->json('training_preferences')->nullable();   // equipo, gustos, limitaciones

            // Salud y antecedentes
            $table->json('injuries')->nullable();
            $table->json('medical_history')->nullable();
            $table->string('apt_fitness_status', 20)->nullable(); // valid|expired|not_required
            $table->date('apt_fitness_expires_at')->nullable();
            $table->string('apt_fitness_file_path')->nullable();
            $table->json('medications_allergies')->nullable();
            $table->string('parq_result', 20)->nullable(); // fit|refer_to_md
            $table->date('parq_date')->nullable();

            // Métricas corporales (último registro)
            $table->decimal('last_weight_kg', 6, 2)->nullable();
            $table->decimal('last_body_fat_pct', 5, 2)->nullable();
            $table->decimal('last_muscle_pct', 5, 2)->nullable();
            $table->decimal('girth_waist_cm', 6, 1)->nullable();
            $table->decimal('girth_hip_cm', 6, 1)->nullable();
            $table->decimal('girth_chest_cm', 6, 1)->nullable();
            $table->decimal('girth_arm_cm', 6, 1)->nullable();
            $table->decimal('girth_thigh_cm', 6, 1)->nullable();

            // Nivel y experiencia
            $table->string('current_level', 20)->nullable(); // beginner|intermediate|advanced
            $table->text('experience_summary')->nullable();

            // Planificación en curso
            $table->foreignId('current_training_phase_id')->nullable()->constrained('training_phases')->nullOnDelete();
            $table->date('plan_start_date')->nullable();
            $table->date('plan_end_date')->nullable();

            // Historial resumido
            $table->unsignedInteger('total_sessions')->nullable();
            $table->decimal('avg_adherence_pct', 5, 1)->nullable();
            $table->text('highlight_prs')->nullable();

            // Comunicación
            $table->foreignId('preferred_channel_id')->nullable()->constrained('communication_channels')->nullOnDelete();
            $table->json('notifications')->nullable(); // {marketing: true, reminders: true, news: false}

            // Administrativo
            $table->string('lead_source')->nullable(); // referido, web, redes, presencial
            $table->longText('private_notes')->nullable();

            // Facturación (si aplica)
            $table->foreignId('commercial_plan_id')->nullable()->constrained('commercial_plans')->nullOnDelete();
            $table->string('billing_frequency', 20)->nullable(); // monthly|quarterly|yearly
            $table->foreignId('preferred_payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->string('account_status', 30)->nullable(); // on_time|due|review

            // Consentimientos y legal
            $table->timestamp('tos_accepted_at')->nullable();
            $table->timestamp('sensitive_data_consent_at')->nullable();
            $table->boolean('image_consent')->default(false);
            $table->timestamp('image_consent_at')->nullable();

            // Relaciones
            $table->json('emergency_contact')->nullable(); // {name, relation, phone}
            $table->json('links_json')->nullable(); // archivos y vínculos varios

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
