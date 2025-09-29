<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exercise_plan_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('exercise_plan_templates', 'deleted_at')) {
                $table->softDeletes()->index();
            }
        });
        Schema::table('exercise_plan_template_workouts', function (Blueprint $table) {
            if (!Schema::hasColumn('exercise_plan_template_workouts', 'deleted_at')) {
                $table->softDeletes()->index();
            }
        });
        Schema::table('exercise_plan_template_blocks', function (Blueprint $table) {
            if (!Schema::hasColumn('exercise_plan_template_blocks', 'deleted_at')) {
                $table->softDeletes()->index();
            }
        });
        Schema::table('exercise_plan_template_items', function (Blueprint $table) {
            if (!Schema::hasColumn('exercise_plan_template_items', 'deleted_at')) {
                $table->softDeletes()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('exercise_plan_templates', fn(Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('exercise_plan_template_workouts', fn(Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('exercise_plan_template_blocks', fn(Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('exercise_plan_template_items', fn(Blueprint $t) => $t->dropSoftDeletes());
    }
};
