<?php

namespace App\Support;

use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;
use App\Models\Central\Manual;

class TenantAwareUrlGenerator extends DefaultUrlGenerator
{
    public function getUrl(): string
    {
        // Obtener la ruta relativa al disco
        $path = $this->getPathRelativeToRoot();

        // Detectar si estamos en contexto tenant
        $isTenant = tenancy()->initialized;

        // Verificar si el media pertenece a un modelo de Central (como Manual)
        $isCentralModel = $this->media->model_type === Manual::class ||
                         str_starts_with($this->media->model_type, 'App\\Models\\Central\\');

        // Si el modelo es de Central, siempre generar URL de Central (sin tenant prefix)
        if ($isCentralModel) {
            // Generar URL apuntando al dominio central
            $centralDomain = config('app.central_domain', config('app.url'));
            $url = rtrim($centralDomain, '/') . '/storage/' . $path;
        } elseif (!$isTenant && ($this->media->disk === 'public' || config('media-library.disk_name') === 'public')) {
            // Central: agregar prefijo /storage
            $url = asset('storage/' . $path);
        } else {
            // Tenant: usar path tal cual viene (ya incluye tenancy/assets/)
            $url = asset($path);
        }

        $url = $this->versionUrl($url);

        return $url;
    }
}
