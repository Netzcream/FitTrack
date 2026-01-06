<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'timezone')) {
                $table->dropColumn('timezone');
            }

            $table->foreignId('user_id')
                ->nullable()
                ->after('uuid')
                ->constrained('users')
                ->cascadeOnDelete();
        });

        // Backfill user_id ensuring each student has a related user
        DB::table('students')->orderBy('id')->chunk(200, function ($rows) {
            foreach ($rows as $row) {
                $name = trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? '')) ?: $row->email;

                $userId = DB::table('users')->where('email', $row->email)->value('id');

                if (! $userId) {
                    $userId = DB::table('users')->insertGetId([
                        'name' => $name,
                        'email' => $row->email,
                        'password' => bcrypt(Str::random(24)),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('students')
                    ->where('id', $row->id)
                    ->update(['user_id' => $userId]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->string('timezone', 64)->nullable()->after('phone');
        });
    }
};
