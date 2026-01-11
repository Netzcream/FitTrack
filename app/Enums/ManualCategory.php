<?php

namespace App\Enums;

enum ManualCategory: string
{
    case CONFIGURATION = 'configuration';
    case TRAINING = 'training';
    case NUTRITION = 'nutrition';
    case SUPPORT = 'support';
    case GENERAL = 'general';

    public function label(): string
    {
        return match($this) {
            self::CONFIGURATION => 'Configuración',
            self::TRAINING => 'Entrenamiento',
            self::NUTRITION => 'Nutrición',
            self::SUPPORT => 'Soporte',
            self::GENERAL => 'General',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::CONFIGURATION => 'settings',
            self::TRAINING => 'dumbbell',
            self::NUTRITION => 'heart',
            self::SUPPORT => 'help-circle',
            self::GENERAL => 'book-open',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
