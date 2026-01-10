<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Configuration;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migra los datos de payment_methods a la tabla de configuraciones.
     */
    public function up(): void
    {
        // Esta migración se ejecuta por tenant, ya que estamos en contexto tenant
        $paymentMethods = DB::table('payment_methods')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->get();

        foreach ($paymentMethods as $method) {
            $code = strtoupper($method->code ?? '');
            $config = json_decode($method->config ?? '{}', true);

            switch ($code) {
                case 'TRANSFER':
                    Configuration::setConf('payment_accepts_transfer', true);

                    // Intentar parsear las instrucciones para extraer datos
                    $instructions = $method->instructions ?? '';
                    Configuration::setConf('payment_bank_name', '');
                    Configuration::setConf('payment_bank_account_holder', '');
                    Configuration::setConf('payment_bank_cuit_cuil', '');
                    Configuration::setConf('payment_bank_cbu', '');
                    Configuration::setConf('payment_bank_alias', '');

                    // Si hay instrucciones, las guardamos en un comentario para el tenant
                    if (!empty($instructions)) {
                        // Podrían migrar manualmente los datos del mensaje viejo
                        logger()->info("Tenant payment TRANSFER instructions: $instructions");
                    }
                    break;

                case 'CARD':
                case 'MERCADOPAGO':
                    Configuration::setConf('payment_accepts_mercadopago', true);
                    Configuration::setConf('payment_mp_access_token', $config['token'] ?? '');
                    Configuration::setConf('payment_mp_public_key', $config['public_key'] ?? '');
                    break;

                case 'CASH':
                    Configuration::setConf('payment_accepts_cash', true);
                    Configuration::setConf('payment_cash_instructions', $method->instructions ?? '');
                    break;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar las configuraciones de métodos de pago
        $keys = [
            'payment_accepts_transfer',
            'payment_bank_name',
            'payment_bank_account_holder',
            'payment_bank_cuit_cuil',
            'payment_bank_cbu',
            'payment_bank_alias',
            'payment_accepts_mercadopago',
            'payment_mp_access_token',
            'payment_mp_public_key',
            'payment_accepts_cash',
            'payment_cash_instructions',
        ];

        DB::table('configurations')->whereIn('key', $keys)->delete();
    }
};
