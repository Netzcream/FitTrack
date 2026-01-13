<?php

namespace App\Traits;

use Illuminate\Support\Arr;

trait HasConfig
{
    protected string $configField = 'config';

    /**
     * Hook automático al usar el trait.
     * Inyecta el campo en $fillable y $casts si no están definidos.
     */
    protected static function bootHasConfig(): void
    {
        static::creating(function ($model) {
            $field = $model->configField ?? 'config';

            // Si el modelo tiene $fillable y no contiene el campo, lo agregamos.
            if (property_exists($model, 'fillable') && !in_array($field, $model->fillable)) {
                $model->fillable[] = $field;
            }

            // Asegurar el cast a array si no está definido
            if (!isset($model->casts[$field])) {
                $model->casts[$field] = 'array';
            }
        });
    }

    /** Devuelve array completo o clave puntual (dot notation) */
    public function config(?string $key = null, $default = null)
    {
        $field = $this->configField;

        if (!array_key_exists($field, $this->attributes)) {
            return is_null($key) ? [] : $default;
        }

        $raw = $this->attributes[$field] ?? null;

        if (is_string($raw) && $raw !== '') {
            $cfg = json_decode($raw, true) ?? [];
        } elseif (is_array($raw)) {
            $cfg = $raw;
        } else {
            $cfg = [];
        }

        return is_null($key) ? $cfg : Arr::get($cfg, $key, $default);
    }

    /** Setea un valor (dot notation). No persiste automáticamente */
    public function setConfigValue(string $key, $value): static
    {
        $cfg = $this->config();
        Arr::set($cfg, $key, $value);

        $field = $this->configField;
        $this->attributes[$field] = json_encode($cfg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $this;
    }

    public function getConfig(string $key, $default = null)
    {
        return $this->config($key, $default);
    }

    public function setConfigField(string $field): static
    {
        $this->configField = $field;
        return $this;
    }
}
