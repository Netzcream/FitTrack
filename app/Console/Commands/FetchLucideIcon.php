<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'icons:lucide', description: 'Descarga iconos de Lucide y genera blades en resources/views/components/icons/lucide')]
class FetchLucideIcon extends Command
{
    /**
     * Firma:
     *   php artisan icons:lucide chevron-down bell calendar --force
     */
    protected $signature = 'icons:lucide
        {name* : Nombre(s) del icono en kebab-case, ej: chevron-down}
        {--force : Sobrescribir si el blade ya existe}';

    protected string $saveBasePath = 'resources/views/components/icons/lucide';

    public function handle(): int
    {
        $names = (array) $this->argument('name');

        // Asegurar carpeta destino
        if (! File::exists(base_path($this->saveBasePath))) {
            File::makeDirectory(base_path($this->saveBasePath), 0775, true);
        }

        $ok = 0;
        $fail = 0;

        foreach ($names as $rawName) {
            $name = Str::of($rawName)
                ->replace([' ', '_'], '-')
                ->lower()
                ->toString();

            $targetPath = base_path("{$this->saveBasePath}/{$name}.blade.php");

            if (File::exists($targetPath) && ! $this->option('force')) {
                $this->warn("· {$name} ya existe. Usa --force para sobrescribir.");
                continue;
            }

            // Intentos de descarga (fuentes oficiales)
            $urls = [
                "https://unpkg.com/lucide-static@latest/icons/{$name}.svg",
                "https://cdn.jsdelivr.net/npm/lucide-static@latest/icons/{$name}.svg",
                "https://raw.githubusercontent.com/lucide-icons/lucide/main/icons/{$name}.svg",
            ];

            $svg = null;
            foreach ($urls as $url) {
                try {
                    $res = Http::timeout(10)->get($url);
                    if ($res->ok() && Str::startsWith($res->header('content-type'), ['image/svg', 'text/plain', 'text/xml'])) {
                        $svg = $res->body();
                        break;
                    }
                } catch (\Throwable $e) {
                    // continuar con la siguiente URL
                }
            }

            if (! $svg) {
                $this->error("× No se pudo descargar {$name}. ¿Existe ese icono en Lucide?");
                $fail++;
                continue;
            }

            // Normalizar: quitar width/height y extraer innerSVG
            // 1) Quitar atributos width/height
            $svg = preg_replace('/\s(width|height)="[^"]*"/i', '', $svg);

            // 2) Extraer viewBox si existe
            $viewBox = '0 0 24 24';
            if (preg_match('/viewBox="([^"]+)"/i', $svg, $m)) {
                $viewBox = $m[1];
            }

            // 3) Extraer contenido interno del <svg>...</svg>
            $inner = $svg;
            if (preg_match('/<svg[^>]*>(.*?)<\/svg>/is', $svg, $m)) {
                $inner = trim($m[1]);
            }

            // 4) Construir blade final
            $className = "lucide lucide-{$name}-icon lucide-{$name}";
            $blade = <<<BLADE
            <svg xmlns="http://www.w3.org/2000/svg" {!! \$attributes !!} viewBox="{$viewBox}" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="{$className}">
                {$inner}
            </svg>
            BLADE;

            // Guardar archivo
            File::put($targetPath, $blade);
            $this->info("✓ {$name} guardado en {$this->saveBasePath}/{$name}.blade.php");
            $ok++;
        }

        $this->line("Listo. Éxitos: {$ok} · Fallos: {$fail}");

        return $fail > 0 ? self::FAILURE : self::SUCCESS;
    }
}
