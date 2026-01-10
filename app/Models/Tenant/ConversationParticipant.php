<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\ParticipantType;

class ConversationParticipant extends Model
{
    protected $connection = 'tenant';
    protected $table = 'conversation_participants';

    public $timestamps = false;

    protected $fillable = [
        'conversation_id',
        'participant_type',
        'participant_id',
        'last_read_at',
        'muted_at',
    ];

    protected $casts = [
        'participant_type' => ParticipantType::class,
        'last_read_at' => 'datetime',
        'muted_at' => 'datetime',
    ];

    /* -------------------------- Relationships -------------------------- */

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /* -------------------------- Scopes -------------------------- */

    public function scopeForParticipant($query, string $type, int $id)
    {
        return $query->where('participant_type', $type)
            ->where('participant_id', $id);
    }

    public function scopeUnmuted($query)
    {
        return $query->whereNull('muted_at');
    }

    /* -------------------------- Helpers -------------------------- */

    public function markAsRead(): void
    {
        $this->update(['last_read_at' => now()]);
    }

    public function mute(): void
    {
        $this->update(['muted_at' => now()]);
    }

    public function unmute(): void
    {
        $this->update(['muted_at' => null]);
    }

    public function isMuted(): bool
    {
        return !is_null($this->muted_at);
    }
}
