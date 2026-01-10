<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\MessageStatus;
use App\Enums\ParticipantType;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class Message extends Model
{
    use SoftDeletes, CentralConnection;

    protected $table = 'messages';

    protected $fillable = [
        'conversation_id',
        'sender_type',
        'sender_id',
        'body',
        'attachments',
        'status',
    ];

    protected $casts = [
        'sender_type' => ParticipantType::class,
        'status' => MessageStatus::class,
        'attachments' => 'array',
    ];

    /* -------------------------- Relationships -------------------------- */

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /* -------------------------- Scopes -------------------------- */

    public function scopeFromSender($query, string $type, int $id)
    {
        return $query->where('sender_type', $type)
            ->where('sender_id', $id);
    }

    public function scopeUnreadBy($query, string $participantType, int $participantId)
    {
        return $query->where('created_at', '>', function ($subQuery) use ($participantType, $participantId) {
            $subQuery->select('last_read_at')
                ->from('conversation_participants')
                ->whereColumn('conversation_id', 'messages.conversation_id')
                ->where('participant_type', $participantType)
                ->where('participant_id', $participantId);
        })->orWhereNull('last_read_at');
    }

    /* -------------------------- Helpers -------------------------- */

    public function markAsDelivered(): void
    {
        $this->update(['status' => MessageStatus::DELIVERED]);
    }

    public function markAsRead(): void
    {
        $this->update(['status' => MessageStatus::READ]);
    }

    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }

    public function getAttachmentCount(): int
    {
        return count($this->attachments ?? []);
    }
}
