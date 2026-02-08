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
     * Obtener branding de forma segura (siempre retorna estructura válida)
     */
    public static function getSafeBrandingData(): array
    {
        try {
            return self::getBrandingData();
        } catch (\Throwable $e) {
            return self::getDefaultBranding();
        }
    }

    /**
     * Obtener trainer de forma segura (siempre retorna estructura válida)
     */
    public static function getSafeTrainerData(): array
    {
        try {
            return self::getTrainerData();
        } catch (\Throwable $e) {
            return self::getDefaultTrainerData();
        }
    }

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
     * Obtener bloque de datos del entrenador para la app mobile.
     * Se envía separado de `data` para evitar romper payloads existentes.
     */
    public static function getTrainerData(): array
    {
        $tenant = Tenancy::getTenant();

        if (!$tenant) {
            return self::getDefaultTrainerData();
        }

        $trainerEmail = self::getTrainerEmail();
        $brandName = self::getBrandName();

        return [
            'name' => self::getTrainerName(),
            'email' => $trainerEmail,
            'brand_name' => $brandName,
            'primary_color' => self::getPrimaryColor(),
            'secondary_color' => self::getSecondaryColor(),
            'accent_color' => self::getAccentColor(),
            'logo_url' => self::getLogoUrl(),
            'logo_light_url' => self::getLogoLightUrl(),
            'contact' => [
                'email' => $trainerEmail,
                'support_email' => self::getContactEmail(),
                'whatsapp' => self::getLandingWhatsapp(),
                'instagram' => self::getLandingInstagram(),
                'facebook' => self::getLandingFacebook(),
                'youtube' => self::getLandingYoutube(),
                'twitter' => self::getLandingTwitter(),
                'tiktok' => self::getLandingTiktok(),
            ],
            'tenant' => [
                'id' => (string) $tenant->id,
                'name' => $tenant->name ?? $brandName,
            ],
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
     * Obtener email de contacto general del tenant
     */
    public static function getContactEmail(): ?string
    {
        return tenant_config('contact_email') ?? null;
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
     * Obtener WhatsApp de contacto
     */
    public static function getLandingWhatsapp(): ?string
    {
        return tenant_config('landing_whatsapp') ?? null;
    }

    /**
     * Obtener usuario de Instagram
     */
    public static function getLandingInstagram(): ?string
    {
        return tenant_config('landing_instagram') ?? null;
    }

    /**
     * Obtener usuario/página de Facebook
     */
    public static function getLandingFacebook(): ?string
    {
        return tenant_config('landing_facebook') ?? null;
    }

    /**
     * Obtener canal de YouTube
     */
    public static function getLandingYoutube(): ?string
    {
        return tenant_config('landing_youtube') ?? null;
    }

    /**
     * Obtener usuario de Twitter/X
     */
    public static function getLandingTwitter(): ?string
    {
        return tenant_config('landing_twitter') ?? null;
    }

    /**
     * Obtener usuario de TikTok
     */
    public static function getLandingTiktok(): ?string
    {
        return tenant_config('landing_tiktok') ?? null;
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

    /**
     * Bloque trainer por defecto si no hay tenant activo
     */
    private static function getDefaultTrainerData(): array
    {
        return [
            'name' => null,
            'email' => null,
            'brand_name' => 'FitTrack',
            'primary_color' => '#3B82F6',
            'secondary_color' => '#10B981',
            'accent_color' => '#F59E0B',
            'logo_url' => null,
            'logo_light_url' => null,
            'contact' => [
                'email' => null,
                'support_email' => 'services@fittrack.com.ar',
                'whatsapp' => null,
                'instagram' => null,
                'facebook' => null,
                'youtube' => null,
                'twitter' => null,
                'tiktok' => null,
            ],
            'tenant' => [
                'id' => null,
                'name' => 'FitTrack',
            ],
        ];
    }
}
