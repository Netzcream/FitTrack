<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Enums\ConversationType;
use Illuminate\Support\Str;

class Conversation extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';
    protected $table = 'conversations';

    protected $fillable = [
        'uuid',
        'type',
        'student_id',
        'subject',
        'last_message_at',
    ];

    protected $casts = [
        'type' => ConversationType::class,
        'last_message_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /* -------------------------- Relationships -------------------------- */

    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'desc');
    }

    public function lastMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /* -------------------------- Scopes -------------------------- */

    public function scopeOfType($query, ConversationType $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeWithUnreadCount($query, string $participantType, int $participantId)
    {
        return $query->withCount(['messages as unread_count' => function ($q) use ($participantType, $participantId) {
            $q->whereRaw('(messages.created_at > COALESCE((
                SELECT last_read_at
                FROM conversation_participants
                WHERE conversation_id = messages.conversation_id
                AND participant_type = ?
                AND participant_id = ?
            ), "1970-01-01 00:00:00"))', [$participantType, $participantId]);
        }]);
    }

    /* -------------------------- Helpers -------------------------- */

    public function addMessage(string $senderType, int $senderId, string $body, ?array $attachments = null): Message
    {
        $message = $this->messages()->create([
            'sender_type' => $senderType,
            'sender_id' => $senderId,
            'body' => $body,
            'attachments' => $attachments,
            'status' => \App\Enums\MessageStatus::SENT,
        ]);

        $this->update(['last_message_at' => now()]);

        return $message;
    }

    public function getUnreadCountForParticipant(string $participantType, int $participantId): int
    {
        $lastReadAt = $this->participants()
            ->where('participant_type', $participantType)
            ->where('participant_id', $participantId)
            ->value('last_read_at');

        if (!$lastReadAt) {
            return $this->messages()->count();
        }

        return $this->messages()
            ->where('created_at', '>', $lastReadAt)
            ->count();
    }
}
