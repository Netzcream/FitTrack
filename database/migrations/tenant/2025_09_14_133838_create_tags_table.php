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
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('code')->unique();     // e.g., MARATHON, POSTPARTUM
            $table->string('color', 7)->nullable(); // #RRGGBB
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        // Initial suggestions (per tenant)
        $now = now();
        DB::table('tags')->insert([
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Marathon',
                'code' => 'MARATHON',
                'color' => '#2563EB', // azul
                'description' => 'Preparación específica para maratón.',
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Postpartum',
                'code' => 'POSTPARTUM',
                'color' => '#10B981', // verde
                'description' => 'Entrenamiento post-parto con progresión segura.',
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
