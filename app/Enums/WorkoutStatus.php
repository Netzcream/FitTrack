<?php

namespace App\Enums;

enum WorkoutStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case SKIPPED = 'skipped';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
            self::SKIPPED => 'Skipped',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::IN_PROGRESS => 'blue',
            self::COMPLETED => 'green',
            self::SKIPPED => 'yellow',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'o-clock',
            self::IN_PROGRESS => 'arrow-right',
            self::COMPLETED => 'check-circle',
            self::SKIPPED => 'x-circle',
        };
    }

    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }
}
