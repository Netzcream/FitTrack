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
        Schema::create('training_phases', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('slug')->nullable()->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        // Initial data (per tenant)
        $now = now();
        DB::table('training_phases')->insert([
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Strength',
                'code' => 'STRENGTH',
                'slug' => 'strength',
                'description' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Hypertrophy',
                'code' => 'HYPERTROPHY',
                'slug' => 'hypertrophy',
                'description' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Maintenance',
                'code' => 'MAINTENANCE',
                'slug' => 'maintenance',
                'description' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::orderedUuid(),
                'name' => 'Deload',
                'code' => 'DELOAD',
                'slug' => 'deload',
                'description' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('training_phases');
    }
};
