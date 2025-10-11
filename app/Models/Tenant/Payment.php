<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\Fit;
use Illuminate\Support\Str;

class Payment extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $table = 'payments';

    protected $fillable = [
        'uuid',
        'student_id',
        'amount',
        'method',
        'status',
        'transaction_id',
        'due_date',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'amount'  => 'decimal:2',
    ];

    /* --------------------------- Boot --------------------------- */
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::orderedUuid();
            }
            if (empty($model->status)) {
                $model->status = 'new';
            }
        });
    }

    /* --------------------------- Scopes -------------------------- */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['cancelled', 'rejected']);
    }

    /* ------------------------- Relaciones ------------------------- */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'method', 'code');
    }

    /* ---------------------- Media Collections --------------------- */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('proofs')
            ->singleFile()
            ->useDisk('tenant')
            ->acceptsFile(fn($file) => in_array($file->mimeType, [
                'image/jpeg', 'image/png', 'application/pdf'
            ]));

        $this->addMediaCollection('receipts')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 200, 200)
            ->performOnCollections('proofs')
            ->nonQueued();
    }

    /* -------------------------- Accessors ------------------------- */
    public function getProofUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('proofs');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
