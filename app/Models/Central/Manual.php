<?php

namespace App\Models\Central;

use App\Enums\ManualCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Concerns\CentralConnection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Manual extends Model implements HasMedia
{
    use SoftDeletes, CentralConnection, InteractsWithMedia;

    protected $table = 'manuals';

    protected $fillable = [
        'uuid',
        'title',
        'slug',
        'category',
        'summary',
        'content',
        'icon_path',
        'is_active',
        'published_at',
        'sort_order',
    ];

    protected $casts = [
        'category' => ManualCategory::class,
        'is_active' => 'boolean',
        'published_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('title') && empty($model->slug)) {
                $model->slug = Str::slug($model->title);
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /* -------------------------- Scopes -------------------------- */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('summary', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%");
        });
    }

    /* -------------------------- Accessors & Mutators -------------------------- */

    public function getExcerptAttribute(): string
    {
        if ($this->summary) {
            return $this->summary;
        }

        return Str::limit(strip_tags($this->content), 150);
    }

    public function getReadingTimeAttribute(): int
    {
        // Aproximadamente 200 palabras por minuto
        $wordCount = str_word_count(strip_tags($this->content));
        return max(1, (int) ceil($wordCount / 200));
    }

    /* -------------------------- Helper Methods -------------------------- */

    public function publish(): void
    {
        $this->update([
            'is_active' => true,
            'published_at' => now(),
        ]);
    }

    public function unpublish(): void
    {
        $this->update([
            'is_active' => false,
        ]);
    }

    /* ------------------------- Media Library setup ------------------------ */

    public function registerMediaCollections(): void
    {
        // Icono del manual (opcional)
        $this->addMediaCollection('icon')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml']);

        // Archivos adjuntos (PDFs, documentos, etc.)
        $this->addMediaCollection('attachments')
            ->acceptsMimeTypes([
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/plain',
                'image/jpeg',
                'image/png',
                'image/webp',
            ]);
    }
}
