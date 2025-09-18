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
        Schema::create('communication_channels', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        // Initial data per tenant
        $now = now();
        DB::table('communication_channels')->insert([
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'App',
                'code' => 'APP',
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'WhatsApp',
                'code' => 'WHATSAPP',
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Email',
                'code' => 'EMAIL',
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_channels');
    }
};
