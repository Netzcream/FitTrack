<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'phone',
        'message',
        'data',
        'unread',
    ];

    protected $casts = [
        'data' => 'array',
        'unread' => 'boolean',
    ];


    public function markAsRead(): void
    {
        $this->update(['unread' => false]);
    }

    public function markAsUnread(): void
    {
        $this->update(['unread' => true]);
    }
    public function scopeUnread($query)
    {
        return $query->where('unread', true);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('uuid', $value)
            ->firstOrFail();
    }
}
