<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('workouts', 'session_instance_id')) {
            Schema::table('workouts', function (Blueprint $table) {
                $table->uuid('session_instance_id')
                    ->nullable()
                    ->after('uuid')
                    ->comment('Run ID único de la sesión de entrenamiento');

                $table->index('session_instance_id', 'workouts_session_instance_id_index');
            });
        }

        DB::table('workouts')
            ->whereNull('session_instance_id')
            ->orderBy('id')
            ->chunkById(500, function ($workouts): void {
                foreach ($workouts as $workout) {
                    $sessionInstanceId = is_string($workout->uuid ?? null) && trim((string) $workout->uuid) !== ''
                        ? (string) $workout->uuid
                        : (string) Str::orderedUuid();

                    DB::table('workouts')
                        ->where('id', $workout->id)
                        ->update([
                            'session_instance_id' => $sessionInstanceId,
                        ]);
                }
            });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('workouts', 'session_instance_id')) {
            return;
        }

        Schema::table('workouts', function (Blueprint $table) {
            $table->dropIndex('workouts_session_instance_id_index');
            $table->dropColumn('session_instance_id');
        });
    }
};

