<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\Fit;

class Student extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $table = 'students';

    protected $fillable = [
        // Identificación
        'uuid',
        'status',

        // Datos personales
        'first_name',
        'last_name',
        'document_number',
        'birth_date',
        'gender',
        'height_cm',
        'weight_kg',

        // Contacto
        'email',
        'phone',
        'timezone',

        // Acceso y perfil
        'is_user_enabled',
        'last_login_at',
        'language',
        'avatar_path',

        // Objetivos y preferencias
        'primary_training_goal_id',
        'secondary_goals',
        'availability_text',
        'training_preferences',

        // Salud y antecedentes
        'injuries',
        'medical_history',
        'apt_fitness_status',
        'apt_fitness_expires_at',
        'apt_fitness_file_path',
        'medications_allergies',
        'parq_result',
        'parq_date',

        // Métricas corporales (último registro)
        'last_weight_kg',
        'last_body_fat_pct',
        'last_muscle_pct',
        'girth_waist_cm',
        'girth_hip_cm',
        'girth_chest_cm',
        'girth_arm_cm',
        'girth_thigh_cm',

        // Nivel y experiencia
        'current_level',
        'experience_summary',

        // Planificación en curso
        'current_training_phase_id',
        'plan_start_date',
        'plan_end_date',

        // Historial resumido
        'total_sessions',
        'avg_adherence_pct',
        'highlight_prs',

        // Comunicación
        'preferred_channel_id',
        'notifications',

        // Administrativo
        'lead_source',
        'private_notes',

        // Facturación
        'commercial_plan_id',
        'billing_frequency',
        'preferred_payment_method_id',
        'account_status',

        // Consentimientos y legal
        'tos_accepted_at',
        'sensitive_data_consent_at',
        'image_consent',
        'image_consent_at',

        // Relaciones
        'emergency_contact',
        'links_json',
    ];

    protected $casts = [
        // Dates / datetimes
        'birth_date'               => 'date',
        'last_login_at'            => 'datetime',
        'apt_fitness_expires_at'   => 'date',
        'parq_date'                => 'date',
        'plan_start_date'          => 'date',
        'plan_end_date'            => 'date',
        'tos_accepted_at'          => 'datetime',
        'sensitive_data_consent_at' => 'datetime',
        'image_consent'            => 'boolean',
        'image_consent_at'         => 'datetime',

        // Numbers
        'height_cm'        => 'decimal:2',
        'weight_kg'        => 'decimal:2',
        'last_weight_kg'   => 'decimal:2',
        'last_body_fat_pct' => 'decimal:2',
        'last_muscle_pct'  => 'decimal:2',
        'girth_waist_cm'   => 'decimal:1',
        'girth_hip_cm'     => 'decimal:1',
        'girth_chest_cm'   => 'decimal:1',
        'girth_arm_cm'     => 'decimal:1',
        'girth_thigh_cm'   => 'decimal:1',
        'avg_adherence_pct' => 'decimal:1',
        'total_sessions'   => 'integer',

        // Json
        'secondary_goals'        => 'array',
        'training_preferences'   => 'array',
        'injuries'               => 'array',
        'medical_history'        => 'array',
        'medications_allergies'  => 'array',
        'notifications'          => 'array',
        'emergency_contact'      => 'array', // {name, relation, phone}
        'links_json'             => 'array', // archivos / vínculos externos
    ];

    /* -------------------------- Accessors útiles -------------------------- */

    public function getImcAttribute(): ?float
    {
        if ($this->weight_kg && $this->height_cm) {
            $m = $this->height_cm / 100;
            return round($this->weight_kg / ($m * $m), 2);
        }
        return null;
    }

    /* ----------------------------- Relaciones ----------------------------- */

    public function commercialPlan()
    {
        return $this->belongsTo(CommercialPlan::class, 'commercial_plan_id');
    }

    public function preferredChannel()
    {
        return $this->belongsTo(CommunicationChannel::class, 'preferred_channel_id');
    }

    public function preferredPaymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'preferred_payment_method_id');
    }

    public function primaryTrainingGoal()
    {
        return $this->belongsTo(TrainingGoal::class, 'primary_training_goal_id');
    }

    public function currentTrainingPhase()
    {
        return $this->belongsTo(TrainingPhase::class, 'current_training_phase_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'student_tag')->withTimestamps();
    }

    /* ------------------------------ UUID boot ----------------------------- */

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::orderedUuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function secondaryTrainingGoals()
    {
        return $this->belongsToMany(TrainingGoal::class, 'student_training_goal')
            ->wherePivot('role', 'secondary')
            ->withTimestamps();
    }
    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
        $this->addMediaCollection('apto')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 64, 64)
            ->performOnCollections('avatar')
            ->nonQueued();
    }
}
