<?php

namespace App\Services\Tenant;

use Stancl\Tenancy\Facades\Tenancy;

/**
 * Servicio para obtener información de branding del tenant actual.
 *
 * Incluye:
 * - Nombre/email del trainer
 * - Logo URL
 * - Colores primarios y secundarios
 * - Nombre de la marca
 *
 * Los valores se obtienen de la tabla `configuration` del tenant.
 */
class BrandingService
{
    /**
     * Obtener datos de branding completos
     */
    public static function getBrandingData(): array
    {
        $tenant = Tenancy::getTenant();

        if (!$tenant) {
            return self::getDefaultBranding();
        }

        return [
            'brand_name' => self::getBrandName(),
            'trainer_email' => self::getTrainerEmail(),
            'trainer_name' => self::getTrainerName(),
            'logo_url' => self::getLogoUrl(),
            'logo_light_url' => self::getLogoLightUrl(),
            'primary_color' => self::getPrimaryColor(),
            'secondary_color' => self::getSecondaryColor(),
            'accent_color' => self::getAccentColor(),
        ];
    }

    /**
     * Obtener nombre de la marca/tenant
     */
    public static function getBrandName(): ?string
    {
        return tenant_config('brand_name') ??
               app('tenant')?->name ??
               null;
    }

    /**
     * Obtener email del trainer
     */
    public static function getTrainerEmail(): ?string
    {
        return tenant_config('trainer_email') ?? null;
    }

    /**
     * Obtener nombre completo del trainer
     */
    public static function getTrainerName(): ?string
    {
        return tenant_config('trainer_name') ?? null;
    }

    /**
     * Obtener URL del logo principal
     */
    public static function getLogoUrl(): ?string
    {
        return tenant_config('logo_url') ?? null;
    }

    /**
     * Obtener URL del logo para fondo claro (light mode)
     */
    public static function getLogoLightUrl(): ?string
    {
        return tenant_config('logo_light_url') ??
               self::getLogoUrl();
    }

    /**
     * Obtener color primario (hex)
     */
    public static function getPrimaryColor(): ?string
    {
        return tenant_config('primary_color') ?? '#3B82F6'; // default: blue
    }

    /**
     * Obtener color secundario (hex)
     */
    public static function getSecondaryColor(): ?string
    {
        return tenant_config('secondary_color') ?? '#10B981'; // default: green
    }

    /**
     * Obtener color de acento (hex)
     */
    public static function getAccentColor(): ?string
    {
        return tenant_config('accent_color') ?? '#F59E0B'; // default: amber
    }

    /**
     * Branding por defecto si no hay tenant activo
     */
    private static function getDefaultBranding(): array
    {
        return [
            'brand_name' => 'FitTrack',
            'trainer_email' => null,
            'trainer_name' => null,
            'logo_url' => null,
            'logo_light_url' => null,
            'primary_color' => '#3B82F6',
            'secondary_color' => '#10B981',
            'accent_color' => '#F59E0B',
        ];
    }
}
