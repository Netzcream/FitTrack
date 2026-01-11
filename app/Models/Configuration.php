<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Obtener el valor de una configuración.
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $value = static::query()->where('key', $key)->value('value');

        if ($value === null) {
            return $default;
        }

        return static::castValue($value);
    }

    /**
     * Establecer el valor de una configuración.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set(string $key, $value): void
    {
        $value = static::prepareValue($value);
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }




    /**
     * Obtener el valor de configuración
     */
    public static function conf(string $key, mixed $default = null): mixed
    {
        $value = static::query()
            ->where('key', $key)
            ->value('value');

        if ($value === null) {
            return $default;
        }

        return static::castValue($value);
    }

    /**
     * Establecer o actualizar un valor
     */
    public static function setConf(string $key, mixed $value): void
    {
        $value = static::prepareValue($value);
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function allAsArray(): array
    {
        return static::pluck('value', 'key')->toArray();
    }

    /**
     * Preparar el valor para guardarlo en la base de datos
     */
    protected static function prepareValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }

    /**
     * Convertir el valor desde la base de datos
     */
    protected static function castValue(string $value): mixed
    {
        // Booleanos
        if ($value === '1' || $value === '0') {
            return $value === '1';
        }

        // JSON
        if (str_starts_with($value, '{') || str_starts_with($value, '[')) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $value;
    }
}
