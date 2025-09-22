<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercise_planes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code')->unique();
            $table->string('name')->unique();   // sagittal, frontal, transverse, multi
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
        });
        /*$now = now();
        DB::table('exercise_planes')->insert([
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Sagital',
                'code' => 'sagittal',
                'description' => 'Movimientos hacia adelante y hacia atrás (flexión, extensión).',
                'order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Frontal',
                'code' => 'frontal',
                'description' => 'Movimientos laterales (abducción, aducción).',
                'order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Transversal',
                'code' => 'transverse',
                'description' => 'Movimientos de rotación (giros, rotaciones internas/externas).',
                'order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Multiplanar',
                'code' => 'multi',
                'description' => 'Movimientos que combinan más de un plano (ej. zancadas con rotación).',
                'order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);*/
    }
    public function down(): void
    {
        Schema::dropIfExists('exercise_planes');
    }
};
