<?php

namespace App\Enums\CommercialPlan;

enum PricingType: string
{
    case ONCE       = 'once';
    case WEEKLY     = 'weekly';
    case BIWEEKLY   = 'biweekly';
    case MONTHLY    = 'monthly';
    case QUARTERLY  = 'quarterly';
    case SEMIANNUAL = 'semiannual';
    case YEARLY     = 'yearly';

    public function label(): string
    {
        return match($this) {
            self::ONCE       => __('commercial_plans.once'),
            self::WEEKLY     => __('commercial_plans.weekly'),
            self::BIWEEKLY   => __('commercial_plans.biweekly'),
            self::MONTHLY    => __('commercial_plans.monthly'),
            self::QUARTERLY  => __('commercial_plans.quarterly'),
            self::SEMIANNUAL => __('commercial_plans.semiannual'),
            self::YEARLY     => __('commercial_plans.yearly'),
        };
    }

    public static function options(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }

    public static function values(): array
    {
        return array_column(self::options(), 'value');
    }
}
