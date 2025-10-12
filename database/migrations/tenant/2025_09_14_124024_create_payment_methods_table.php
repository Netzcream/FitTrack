<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('code')->unique();              // e.g., TRANSFER, CARD, CASH, MERCADOPAGO
            $table->text('description')->nullable();       // breve descripción del método
            $table->text('instructions')->nullable();      // instrucciones libres: CBU, descuentos, etc.
            $table->json('config')->nullable();            // credenciales o settings (provider, token, etc.)
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        // Initial data per tenant
        $now = now();
        DB::table('payment_methods')->insert([
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Bank Transfer',
                'code' => 'TRANSFER',
                'description' => 'Transferencia bancaria / depósito',
                'instructions' => 'Ingresá el CBU/alias en la configuración del entrenador.',
                'config' => json_encode(['provider' => null, 'token' => null]),
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Card',
                'code' => 'CARD',
                'description' => 'Pago con tarjeta (pasarela genérica)',
                'instructions' => 'Podés conectar una pasarela (ej: Mercado Pago) configurando el token.',
                'config' => json_encode(['provider' => null, 'token' => null]),
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Cash',
                'code' => 'CASH',
                'description' => 'Pago en efectivo',
                'instructions' => 'Hasta 5% de descuento pagando antes del vencimiento.',
                'config' => json_encode(['provider' => null]),
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
