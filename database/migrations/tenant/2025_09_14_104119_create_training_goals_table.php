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
        Schema::create('training_goals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->string('code')->unique()->index();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        $now = now();

        DB::table('training_goals')->insert([
            [
                'uuid'       => (string) Str::uuid(),
                'code'       => 'fat_loss',
                'name'       => 'Fat loss',
                'is_active'  => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'uuid'       => (string) Str::uuid(),
                'code'       => 'muscle_gain',
                'name'       => 'Muscle gain',
                'is_active'  => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'uuid'       => (string) Str::uuid(),
                'code'       => 'performance',
                'name'       => 'Performance',
                'is_active'  => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'uuid'       => (string) Str::uuid(),
                'code'       => 'rehabilitation',
                'name'       => 'Rehabilitation',
                'is_active'  => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('training_goals');
    }
};
