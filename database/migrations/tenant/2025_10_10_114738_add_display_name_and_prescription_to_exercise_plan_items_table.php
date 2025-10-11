<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exercise_plan_items', function (Blueprint $table) {
            $table->string('display_name')->nullable()->after('exercise_id');
            $table->json('prescription')->nullable()->after('load_prescription');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exercise_plan_items', function (Blueprint $table) {
            $table->dropColumn(['display_name', 'prescription']);
        });
    }
};
