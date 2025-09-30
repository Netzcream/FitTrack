<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) Ampliar ENUM (union de viejo + nuevo), mantener default válido
        DB::statement("
            ALTER TABLE `exercise_plan_blocks`
            MODIFY `type` ENUM(
                'normal','superset','circuit','giantset',     -- valores viejos
                'warmup','main','accessory','conditioning','cooldown','other' -- nuevos
            ) NOT NULL DEFAULT 'normal'
        ");

        // 2) Normalizar datos:
        //   normal   -> main
        //   superset -> accessory
        //   giantset -> accessory
        //   circuit  -> conditioning
        DB::statement("UPDATE `exercise_plan_blocks` SET `type` = 'main'         WHERE `type` IN ('normal')");
        DB::statement("UPDATE `exercise_plan_blocks` SET `type` = 'accessory'    WHERE `type` IN ('superset','giantset')");
        DB::statement("UPDATE `exercise_plan_blocks` SET `type` = 'conditioning' WHERE `type` IN ('circuit')");
        // Cualquier otro valor raro -> other
        DB::statement("UPDATE `exercise_plan_blocks` SET `type` = 'other'        WHERE `type` NOT IN ('warmup','main','accessory','conditioning','cooldown','other')");

        // 3) Reducir ENUM a la lista final con default 'main'
        DB::statement("
            ALTER TABLE `exercise_plan_blocks`
            MODIFY `type` ENUM('warmup','main','accessory','conditioning','cooldown','other')
            NOT NULL DEFAULT 'main'
        ");
    }

    public function down(): void
    {
        // Volver al enum viejo (si hay valores nuevos, fallará — opcional)
        DB::statement("
            ALTER TABLE `exercise_plan_blocks`
            MODIFY `type` ENUM('normal','superset','circuit','giantset') NOT NULL DEFAULT 'normal'
        ");
    }
};
