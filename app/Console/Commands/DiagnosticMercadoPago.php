<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stancl\Tenancy\Tenancy;
use App\Models\Tenant;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;

class DiagnosticMercadoPago extends Command
{
    protected $signature = 'mp:diagnostic {tenant=sabrina : Tenant ID}';
    protected $description = 'Diagnóstico de configuración de Mercado Pago para un tenant';

    public function handle()
    {
        $tenantId = $this->argument('tenant');
        $this->info("=== DIAGNÓSTICO DE MERCADO PAGO PARA TENANT: {$tenantId} ===");
        $this->newLine();

        // 1. Obtener y inicializar el tenant
        $this->info('1. Inicializando tenant:');
        try {
            $tenant = Tenant::where('id', $tenantId)->first();
            if (!$tenant) {
                $this->error("   ✗ Tenant '{$tenantId}' no encontrado");
                return;
            }
            tenancy()->initialize($tenant);
            $this->line("   ✓ Tenant inicializado: {$tenant->id}");
        } catch (\Throwable $e) {
            $this->error("   ✗ Error inicializando tenant: " . $e->getMessage());
            return;
        }

        $this->newLine();

        // 2. Revisar token
        $this->info('2. Access Token:');
        try {
            $token = tenant_config('payment_mp_access_token');
            if (!$token) {
                $this->error('   ✗ NO está configurado');
                $this->line('   → Debes configurar el token en la tabla Configuration del tenant');
                return;
            }

            $prefix = substr($token, 0, 30);
            $this->line("   ✓ Configurado: {$prefix}...");

            // Validar que sea de sandbox (APP_USR_)
            if (str_starts_with($token, 'APP_USR_')) {
                $this->line("   ✓ Es token de TEST (APP_USR_...) ✓");
            } else {
                $this->warn("   ⚠ NO empieza con APP_USR_ - podría ser producción");
            }
        } catch (\Throwable $e) {
            $this->error("   ✗ Error leyendo token: " . $e->getMessage());
            return;
        }

        $this->newLine();

        // 3. Verificar configuración SDK
        $this->info('3. Configuración del SDK:');
        try {
            MercadoPagoConfig::setAccessToken($token);
            MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);
            $this->line("   ✓ SDK inicializado");
            $this->line("   ✓ Runtime Environment: LOCAL (Sandbox)");
        } catch (\Throwable $e) {
            $this->error("   ✗ Error configurando SDK: " . $e->getMessage());
            return;
        }

        $this->newLine();

        // 4. Probar conexión
        $this->info('4. Prueba de conexión con Mercado Pago:');
        try {
            $client = new PreferenceClient();

            $testPayload = [
                'items' => [[
                    'title' => 'Test de diagnóstico',
                    'quantity' => 1,
                    'unit_price' => 100,
                    'currency_id' => 'ARS',
                ]],
                'external_reference' => 'TEST-' . time(),
            ];

            $this->line("   → Enviando payload de prueba...");
            $preference = $client->create($testPayload);

            $this->line("   ✓ Conexión exitosa!");
            $this->line("   ✓ Preference ID: " . $preference->id);
            $this->line("   ✓ Init Point: " . substr($preference->init_point, 0, 50) . "...");
        } catch (\MercadoPago\Exceptions\MPApiException $e) {
            $this->error("   ✗ Error de API de Mercado Pago:");
            $this->line("      Status: " . $e->getStatusCode());
            $this->line("      Message: " . $e->getMessage());
            if ($e->getApiResponse()) {
                $response = $e->getApiResponse()->getContent();
                $this->line("      Response: " . json_encode($response, JSON_PRETTY_PRINT));

                // Análisis específico del error
                if ($e->getStatusCode() === 400) {
                    $this->warn("\n   ⚠ Error 400 - Mala solicitud. Posibles causas:");
                    if (isset($response['message'])) {
                        $msg = $response['message'];
                        if (str_contains($msg, 'test')) {
                            $this->warn("      → El token es de producción pero el ambiente es sandbox");
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            $this->error("   ✗ Error: " . $e->getMessage());
            $this->line("      Trace: " . $e->getTraceAsString());
        }

        $this->newLine();
        $this->info('=== FIN DEL DIAGNÓSTICO ===');
    }
}
