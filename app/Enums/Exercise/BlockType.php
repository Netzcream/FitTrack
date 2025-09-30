<?php

namespace App\Enums\Exercise;

enum BlockType: string
{
    case Warmup       = 'warmup';
    case Main         = 'main';
    case Accessory    = 'accessory';
    case Conditioning = 'conditioning';
    case Cooldown     = 'cooldown';
    case Other        = 'other';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Warmup       => __('Calentamiento'),
            self::Main         => __('Principal'),
            self::Accessory    => __('Accesorio'),
            self::Conditioning => __('Acondicionamiento'),
            self::Cooldown     => __('Enfriamiento'),
            self::Other        => __('Otro'),
        };
    }
}
