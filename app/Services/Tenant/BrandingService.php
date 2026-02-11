<?php

namespace App\Services\Tenant;

/**
 * Servicio para obtener branding/contacto del tenant actual para API mobile.
 *
 * Fuentes principales:
 * - Nombre del centro: tenant->name (Configuracion > General)
 * - Colores del sitio: color_base/color_dark/color_light
 * - Footer: footer_text_color/footer_background_color (+ fallback landing_*)
 * - Contacto: trainer_*, contact_email y redes landing_*
 */
class BrandingService
{
    private const DEFAULT_BRAND_NAME = 'FitTrack';
    private const DEFAULT_SUPPORT_EMAIL = 'services@fittrack.com.ar';
    private const DEFAULT_COLOR_BASE = '#263d83';
    private const DEFAULT_COLOR_DARK = '#1d2d5e';
    private const DEFAULT_COLOR_LIGHT = '#f9fafb';
    private const DEFAULT_FOOTER_TEXT_COLOR = '#000000';
    private const DEFAULT_FOOTER_BG_COLOR = '#ffffff';

    /**
     * Obtener branding de forma segura (siempre retorna estructura valida)
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
     * Obtener trainer de forma segura (siempre retorna estructura valida)
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
        $tenant = self::getCurrentTenant();

        if (!$tenant) {
            return self::getDefaultBranding();
        }

        $brandName = self::getBrandName($tenant);
        $trainerEmail = self::getTrainerEmail();
        $trainerName = self::getTrainerName($tenant, $brandName);
        $logoUrl = self::getLogoUrl($tenant);
        $logoLightUrl = self::getLogoLightUrl($tenant, $logoUrl);
        $faviconUrl = self::getFaviconUrl($tenant);

        $colorBase = self::getColorBase();
        $colorDark = self::getColorDark();
        $colorLight = self::getColorLight();
        $footerTextColor = self::getFooterTextColor();
        $footerBackgroundColor = self::getFooterBackgroundColor();

        return [
            'brand_name' => $brandName,
            'trainer_email' => $trainerEmail,
            'trainer_name' => $trainerName,
            'logo_url' => $logoUrl,
            'logo_light_url' => $logoLightUrl,
            'favicon_url' => $faviconUrl,
            'primary_color' => self::getPrimaryColor($colorBase),
            'secondary_color' => self::getSecondaryColor($colorDark),
            'accent_color' => self::getAccentColor($colorLight),
            'color_base' => $colorBase,
            'color_dark' => $colorDark,
            'color_light' => $colorLight,
            'footer_text_color' => $footerTextColor,
            'footer_background_color' => $footerBackgroundColor,
            'css_variables' => self::buildCssVariables(
                $colorBase,
                $colorDark,
                $colorLight,
                $footerTextColor,
                $footerBackgroundColor
            ),
        ];
    }

    /**
     * Obtener bloque de datos del entrenador para la app mobile.
     * Se envia separado de `data` para evitar romper payloads existentes.
     */
    public static function getTrainerData(): array
    {
        $tenant = self::getCurrentTenant();

        if (!$tenant) {
            return self::getDefaultTrainerData();
        }

        $branding = self::getBrandingData();
        $trainerEmail = self::getTrainerEmail();

        return [
            'name' => self::getTrainerName($tenant, $branding['brand_name']),
            'email' => $trainerEmail,
            'brand_name' => $branding['brand_name'],
            'primary_color' => $branding['primary_color'],
            'secondary_color' => $branding['secondary_color'],
            'accent_color' => $branding['accent_color'],
            'logo_url' => $branding['logo_url'],
            'logo_light_url' => $branding['logo_light_url'],
            'favicon_url' => $branding['favicon_url'],
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
            'theme' => [
                'color_base' => $branding['color_base'],
                'color_dark' => $branding['color_dark'],
                'color_light' => $branding['color_light'],
                'footer_text_color' => $branding['footer_text_color'],
                'footer_background_color' => $branding['footer_background_color'],
                'css_variables' => $branding['css_variables'],
            ],
            'tenant' => [
                'id' => (string) $tenant->id,
                'name' => self::cleanString($tenant->name) ?? $branding['brand_name'],
            ],
        ];
    }

    /**
     * Obtener nombre de la marca/tenant
     */
    public static function getBrandName($tenant = null): string
    {
        $tenant ??= self::getCurrentTenant();
        $tenantName = self::cleanString($tenant?->name ?? null);
        if ($tenantName !== null) {
            return $tenantName;
        }

        $configured = self::cleanString(tenant_config('brand_name'));
        if ($configured !== null) {
            return $configured;
        }

        $landingTitle = self::cleanString(tenant_config('landing_title'));
        if ($landingTitle !== null) {
            return $landingTitle;
        }

        return self::DEFAULT_BRAND_NAME;
    }

    /**
     * Obtener email del trainer
     */
    public static function getTrainerEmail(): ?string
    {
        $trainerEmail = self::cleanString(tenant_config('trainer_email'));
        if ($trainerEmail !== null) {
            return $trainerEmail;
        }

        return self::cleanString(tenant_config('contact_email'));
    }

    /**
     * Obtener nombre del trainer (o nombre del centro como fallback)
     */
    public static function getTrainerName($tenant = null, ?string $fallbackBrandName = null): ?string
    {
        $trainerName = self::cleanString(tenant_config('trainer_name'));
        if ($trainerName !== null) {
            return $trainerName;
        }

        $tenant ??= self::getCurrentTenant();
        $tenantName = self::cleanString($tenant?->name ?? null);
        if ($tenantName !== null) {
            return $tenantName;
        }

        return $fallbackBrandName ?? self::DEFAULT_BRAND_NAME;
    }

    /**
     * Obtener email de contacto general del tenant
     */
    public static function getContactEmail(): string
    {
        return self::cleanString(tenant_config('contact_email')) ?? self::DEFAULT_SUPPORT_EMAIL;
    }

    /**
     * Obtener URL del logo principal
     */
    public static function getLogoUrl($tenant = null): ?string
    {
        $tenant ??= self::getCurrentTenant();

        $fromConfig = self::cleanString(tenant_config('logo_url'));
        if ($fromConfig !== null) {
            return self::normalizeTenantMediaUrl($fromConfig, $tenant);
        }

        try {
            $fromMedia = self::cleanString($tenant->config?->getFirstMediaUrl('logo'));
            if ($fromMedia !== null) {
                return self::normalizeTenantMediaUrl($fromMedia, $tenant);
            }
        } catch (\Throwable $e) {
            // fallback below
        }

        return null;
    }

    /**
     * Obtener URL del logo para fondo claro (light mode)
     */
    public static function getLogoLightUrl($tenant = null, ?string $fallbackLogoUrl = null): ?string
    {
        $tenant ??= self::getCurrentTenant();

        $fromConfig = self::cleanString(tenant_config('logo_light_url'));
        if ($fromConfig !== null) {
            return self::normalizeTenantMediaUrl($fromConfig, $tenant);
        }

        try {
            $fromMedia = self::cleanString($tenant->config?->getFirstMediaUrl('logo_light'));
            if ($fromMedia !== null) {
                return self::normalizeTenantMediaUrl($fromMedia, $tenant);
            }
        } catch (\Throwable $e) {
            // fallback below
        }

        $fallback = $fallbackLogoUrl ?? self::getLogoUrl($tenant);
        return self::normalizeTenantMediaUrl($fallback, $tenant);
    }

    /**
     * Obtener URL del favicon
     */
    public static function getFaviconUrl($tenant = null): ?string
    {
        $tenant ??= self::getCurrentTenant();

        $fromConfig = self::cleanString(tenant_config('favicon_url'));
        if ($fromConfig !== null) {
            return self::normalizeTenantMediaUrl($fromConfig, $tenant);
        }

        try {
            $fromMedia = self::cleanString($tenant->config?->getFirstMediaUrl('favicon'));
            if ($fromMedia !== null) {
                return self::normalizeTenantMediaUrl($fromMedia, $tenant);
            }
        } catch (\Throwable $e) {
            // fallback below
        }

        return null;
    }

    /**
     * Obtener color base (hex)
     */
    public static function getColorBase(): string
    {
        return self::normalizeHexColor(tenant_config('color_base'), self::DEFAULT_COLOR_BASE);
    }

    /**
     * Obtener color dark (hex)
     */
    public static function getColorDark(): string
    {
        return self::normalizeHexColor(tenant_config('color_dark'), self::DEFAULT_COLOR_DARK);
    }

    /**
     * Obtener color light (hex)
     */
    public static function getColorLight(): string
    {
        return self::normalizeHexColor(tenant_config('color_light'), self::DEFAULT_COLOR_LIGHT);
    }

    /**
     * Obtener color de texto de footer (hex)
     */
    public static function getFooterTextColor(): string
    {
        $value = tenant_config('footer_text_color');
        if ($value === null) {
            $value = tenant_config('landing_footer_text_color');
        }

        return self::normalizeHexColor($value, self::DEFAULT_FOOTER_TEXT_COLOR);
    }

    /**
     * Obtener color de fondo de footer (hex)
     */
    public static function getFooterBackgroundColor(): string
    {
        $value = tenant_config('footer_background_color');
        if ($value === null) {
            $value = tenant_config('landing_footer_background_color');
        }

        return self::normalizeHexColor($value, self::DEFAULT_FOOTER_BG_COLOR);
    }

    /**
     * Obtener color primario (hex)
     */
    public static function getPrimaryColor(?string $fallbackColorBase = null): string
    {
        $default = $fallbackColorBase ?? self::getColorBase();
        return self::normalizeHexColor(tenant_config('primary_color'), $default);
    }

    /**
     * Obtener color secundario (hex)
     */
    public static function getSecondaryColor(?string $fallbackColorDark = null): string
    {
        $default = $fallbackColorDark ?? self::getColorDark();
        return self::normalizeHexColor(tenant_config('secondary_color'), $default);
    }

    /**
     * Obtener color de acento (hex)
     */
    public static function getAccentColor(?string $fallbackColorLight = null): string
    {
        $default = $fallbackColorLight ?? self::getColorLight();
        return self::normalizeHexColor(tenant_config('accent_color'), $default);
    }

    /**
     * Obtener WhatsApp de contacto
     */
    public static function getLandingWhatsapp(): ?string
    {
        return self::cleanString(tenant_config('landing_whatsapp'));
    }

    /**
     * Obtener usuario de Instagram
     */
    public static function getLandingInstagram(): ?string
    {
        return self::cleanString(tenant_config('landing_instagram'));
    }

    /**
     * Obtener usuario/pagina de Facebook
     */
    public static function getLandingFacebook(): ?string
    {
        return self::cleanString(tenant_config('landing_facebook'));
    }

    /**
     * Obtener canal de YouTube
     */
    public static function getLandingYoutube(): ?string
    {
        return self::cleanString(tenant_config('landing_youtube'));
    }

    /**
     * Obtener usuario de Twitter/X
     */
    public static function getLandingTwitter(): ?string
    {
        return self::cleanString(tenant_config('landing_twitter'));
    }

    /**
     * Obtener usuario de TikTok
     */
    public static function getLandingTiktok(): ?string
    {
        return self::cleanString(tenant_config('landing_tiktok'));
    }

    /**
     * Branding por defecto si no hay tenant activo
     */
    private static function getDefaultBranding(): array
    {
        $css = self::buildCssVariables(
            self::DEFAULT_COLOR_BASE,
            self::DEFAULT_COLOR_DARK,
            self::DEFAULT_COLOR_LIGHT,
            self::DEFAULT_FOOTER_TEXT_COLOR,
            self::DEFAULT_FOOTER_BG_COLOR
        );

        return [
            'brand_name' => self::DEFAULT_BRAND_NAME,
            'trainer_email' => null,
            'trainer_name' => self::DEFAULT_BRAND_NAME,
            'logo_url' => null,
            'logo_light_url' => null,
            'favicon_url' => null,
            'primary_color' => self::DEFAULT_COLOR_BASE,
            'secondary_color' => self::DEFAULT_COLOR_DARK,
            'accent_color' => self::DEFAULT_COLOR_LIGHT,
            'color_base' => self::DEFAULT_COLOR_BASE,
            'color_dark' => self::DEFAULT_COLOR_DARK,
            'color_light' => self::DEFAULT_COLOR_LIGHT,
            'footer_text_color' => self::DEFAULT_FOOTER_TEXT_COLOR,
            'footer_background_color' => self::DEFAULT_FOOTER_BG_COLOR,
            'css_variables' => $css,
        ];
    }

    /**
     * Bloque trainer por defecto si no hay tenant activo
     */
    private static function getDefaultTrainerData(): array
    {
        $branding = self::getDefaultBranding();

        return [
            'name' => self::DEFAULT_BRAND_NAME,
            'email' => null,
            'brand_name' => self::DEFAULT_BRAND_NAME,
            'primary_color' => $branding['primary_color'],
            'secondary_color' => $branding['secondary_color'],
            'accent_color' => $branding['accent_color'],
            'logo_url' => null,
            'logo_light_url' => null,
            'favicon_url' => null,
            'contact' => [
                'email' => null,
                'support_email' => self::DEFAULT_SUPPORT_EMAIL,
                'whatsapp' => null,
                'instagram' => null,
                'facebook' => null,
                'youtube' => null,
                'twitter' => null,
                'tiktok' => null,
            ],
            'theme' => [
                'color_base' => $branding['color_base'],
                'color_dark' => $branding['color_dark'],
                'color_light' => $branding['color_light'],
                'footer_text_color' => $branding['footer_text_color'],
                'footer_background_color' => $branding['footer_background_color'],
                'css_variables' => $branding['css_variables'],
            ],
            'tenant' => [
                'id' => null,
                'name' => self::DEFAULT_BRAND_NAME,
            ],
        ];
    }

    /**
     * Armar css variables del frontend como payload API reutilizable
     */
    private static function buildCssVariables(
        string $colorBase,
        string $colorDark,
        string $colorLight,
        string $footerTextColor,
        string $footerBackgroundColor
    ): array {
        return [
            'color_base' => $colorBase,
            'color_dark' => $colorDark,
            'color_light' => $colorLight,
            'color_base_transparent' => self::withAlpha($colorBase, '55'),
            'color_base_bright' => self::withAlpha($colorBase, 'CC'),
            'color_dark_transparent' => self::withAlpha($colorDark, '55'),
            'color_light_transparent' => self::withAlpha($colorLight, '55'),
            'text_footer' => $footerTextColor,
            'background_footer_transparent' => self::withAlpha($footerBackgroundColor, '55'),
        ];
    }

    /**
     * Obtener tenant actual sin lanzar excepción
     */
    private static function getCurrentTenant(): mixed
    {
        try {
            $fromHelper = tenant();
            if ($fromHelper !== null) {
                return $fromHelper;
            }
        } catch (\Throwable $e) {
            // try other resolvers
        }

        try {
            if (app()->bound('tenant')) {
                $fromContainer = app('tenant');
                if ($fromContainer !== null) {
                    return $fromContainer;
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            return tenancy()->tenant ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Normaliza colores hex; acepta abc / aabbcc / #abc / #aabbcc
     */
    private static function normalizeHexColor(mixed $value, string $default): string
    {
        $candidate = self::cleanString($value);
        if ($candidate === null) {
            return strtolower($default);
        }

        if (!str_starts_with($candidate, '#')) {
            $candidate = '#' . $candidate;
        }

        $hex = substr($candidate, 1);

        if (preg_match('/^[0-9a-fA-F]{3}$/', $hex) === 1) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (preg_match('/^[0-9a-fA-F]{6}$/', $hex) !== 1) {
            return strtolower($default);
        }

        return '#' . strtolower($hex);
    }

    /**
     * Aplica canal alfa hexadecimal sobre un color base #rrggbb
     */
    private static function withAlpha(string $hexColor, string $alpha): string
    {
        $base = self::normalizeHexColor($hexColor, self::DEFAULT_COLOR_BASE);
        $alpha = strtoupper($alpha);

        if (preg_match('/^[0-9A-F]{2}$/', $alpha) !== 1) {
            $alpha = 'FF';
        }

        return $base . $alpha;
    }

    /**
     * Si la URL apunta a tenancy/assets, forzar host de tenant.
     */
    private static function normalizeTenantMediaUrl(?string $url, mixed $tenant): ?string
    {
        if ($url === null) {
            return null;
        }

        if (!$tenant) {
            return $url;
        }

        $tenantBaseUrl = self::buildTenantBaseUrl($tenant);
        if ($tenantBaseUrl === null) {
            return $url;
        }

        if (str_starts_with($url, '/tenancy/assets/') || str_starts_with($url, 'tenancy/assets/')) {
            return rtrim($tenantBaseUrl, '/') . '/' . ltrim($url, '/');
        }

        if (!str_contains($url, '/tenancy/assets/')) {
            return $url;
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path) || trim($path) === '') {
            return $url;
        }

        $path = ltrim($path, '/');
        $position = strpos($path, 'tenancy/assets/');
        if ($position === false) {
            return $url;
        }

        $tenantAssetPath = substr($path, $position);
        return rtrim($tenantBaseUrl, '/') . '/' . $tenantAssetPath;
    }

    private static function buildTenantBaseUrl(mixed $tenant): ?string
    {
        $domain = self::cleanString($tenant?->domains()->orderBy('id')->value('domain'));

        if ($domain === null) {
            $appDomain = self::cleanString(env('APP_DOMAIN'));
            if ($appDomain !== null) {
                $domain = (string) $tenant->id . '.' . $appDomain;
            }
        }

        if ($domain === null) {
            return null;
        }

        if (str_starts_with($domain, 'http://') || str_starts_with($domain, 'https://')) {
            return rtrim($domain, '/');
        }

        return self::resolveScheme() . '://' . ltrim($domain, '/');
    }

    private static function resolveScheme(): string
    {
        try {
            if (app()->bound('request')) {
                $request = request();
                if ($request && method_exists($request, 'getScheme')) {
                    return $request->getScheme();
                }
            }
        } catch (\Throwable $e) {
            // fallback below
        }

        $appUrlScheme = parse_url((string) config('app.url'), PHP_URL_SCHEME);
        if (is_string($appUrlScheme) && in_array($appUrlScheme, ['http', 'https'], true)) {
            return $appUrlScheme;
        }

        return app()->environment('local') ? 'http' : 'https';
    }

    /**
     * Limpia strings vacíos o con espacios
     */
    private static function cleanString(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $trimmed = trim($value);
        return $trimmed === '' ? null : $trimmed;
    }
}
