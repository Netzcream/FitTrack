<?php

namespace App\Support;

use App\Models\Central\Manual;
use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;

class TenantAwareUrlGenerator extends DefaultUrlGenerator
{
    public function getUrl(): string
    {
        $path = ltrim($this->getPathRelativeToRoot(), '/');
        $isTenant = tenancy()->initialized;

        // Si pertenece a modelos centrales, siempre usar dominio central.
        if ($this->isCentralModel()) {
            $url = $this->buildCentralUrl($path);
            return $this->versionUrl($url);
        }

        // Si estamos en tenant, forzar host de tenant para cualquier media.
        if ($isTenant) {
            $tenantBaseUrl = $this->resolveTenantBaseUrl();
            if ($tenantBaseUrl !== null) {
                $tenantAssetPath = $this->normalizeTenantAssetPath($path);
                $url = rtrim($tenantBaseUrl, '/') . '/' . $tenantAssetPath;
                return $this->versionUrl($url);
            }
        }

        // Contexto central o fallback.
        if ($this->media->disk === 'public' || config('media-library.disk_name') === 'public') {
            $url = asset('storage/' . $path);
            return $this->versionUrl($url);
        }

        $url = asset($path);
        return $this->versionUrl($url);
    }

    private function isCentralModel(): bool
    {
        return $this->media->model_type === Manual::class
            || str_starts_with($this->media->model_type, 'App\\Models\\Central\\');
    }

    private function buildCentralUrl(string $path): string
    {
        $centralDomain = config('app.central_domain', config('app.url'));
        return rtrim((string) $centralDomain, '/') . '/storage/' . $path;
    }

    private function resolveTenantBaseUrl(): ?string
    {
        $tenant = tenant();
        if (!$tenant) {
            return null;
        }

        $domain = $tenant->domains()->orderBy('id')->value('domain');
        if (!is_string($domain) || trim($domain) === '') {
            $appDomain = trim((string) env('APP_DOMAIN', ''));
            if ($appDomain !== '') {
                $domain = $tenant->id . '.' . $appDomain;
            }
        }

        if (!is_string($domain) || trim($domain) === '') {
            return null;
        }

        $domain = trim($domain);
        if (str_starts_with($domain, 'http://') || str_starts_with($domain, 'https://')) {
            return rtrim($domain, '/');
        }

        return $this->resolveScheme() . '://' . ltrim($domain, '/');
    }

    private function resolveScheme(): string
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

    private function normalizeTenantAssetPath(string $path): string
    {
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'tenancy/assets/')) {
            return $path;
        }

        return 'tenancy/assets/' . $path;
    }
}
