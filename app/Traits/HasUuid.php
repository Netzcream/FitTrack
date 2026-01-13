<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    /**
     * Se ejecuta automáticamente cuando el trait se carga.
     * No interfiere con boot() ni booted() del modelo.
     */
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::orderedUuid();
            }
        });
    }

    /**
     * Asegura que la columna 'uuid' siempre esté presente en fillables
     * si no fue definida manualmente en el modelo.
     */
    public function initializeHasUuid(): void
    {
        if (!in_array('uuid', $this->fillable ?? [])) {
            $this->fillable[] = 'uuid';
        }
    }

    /**
     * Configura el route model binding para usar UUID
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
