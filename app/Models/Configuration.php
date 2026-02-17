<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        return static::conf($key, $default);
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
        static::setConf($key, $value);
    }




    /**
     * Obtener el valor de configuración
     */
    public static function conf(string $key, mixed $default = null): mixed
    {
        if (static::usesTenantConfigurations()) {
            $tenantId = tenancy()->initialized ? (string) tenancy()->tenant?->id : null;
            if (! $tenantId) {
                return $default;
            }

            $row = DB::table('tenant_configurations')->where('tenant_id', $tenantId)->first();
            if (! $row) {
                return $default;
            }

            $data = is_array($row->data) ? $row->data : (json_decode($row->data ?? '{}', true) ?: []);
            if (! array_key_exists($key, $data)) {
                return $default;
            }

            $value = $data[$key];
            if (is_string($value)) {
                return static::castValue($value);
            }

            return $value;
        }

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
        if (static::usesTenantConfigurations()) {
            $tenantId = tenancy()->initialized ? (string) tenancy()->tenant?->id : null;
            if (! $tenantId) {
                return;
            }

            $row = DB::table('tenant_configurations')->where('tenant_id', $tenantId)->first();
            $data = $row
                ? (is_array($row->data) ? $row->data : (json_decode($row->data ?? '{}', true) ?: []))
                : [];

            $data[$key] = $value;

            if ($row) {
                DB::table('tenant_configurations')
                    ->where('tenant_id', $tenantId)
                    ->update(['data' => json_encode($data), 'updated_at' => now()]);
            } else {
                DB::table('tenant_configurations')->insert([
                    'tenant_id' => $tenantId,
                    'data' => json_encode($data),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return;
        }

        $value = static::prepareValue($value);
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function allAsArray(): array
    {
        if (static::usesTenantConfigurations()) {
            $tenantId = tenancy()->initialized ? (string) tenancy()->tenant?->id : null;
            if (! $tenantId) {
                return [];
            }

            $row = DB::table('tenant_configurations')->where('tenant_id', $tenantId)->first();
            if (! $row) {
                return [];
            }

            return is_array($row->data) ? $row->data : (json_decode($row->data ?? '{}', true) ?: []);
        }

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

    protected static function usesTenantConfigurations(): bool
    {
        if (! function_exists('tenancy')) {
            return false;
        }

        if (! tenancy()->initialized) {
            return false;
        }

        return Schema::hasTable('tenant_configurations');
    }
}
