<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Renombrar tabla
        Schema::rename('configurations', 'tenant_configurations');

        // Alterar estructura
        Schema::table('tenant_configurations', function (Blueprint $table) {
            $table->dropUnique('configurations_key_unique');
            $table->dropColumn(['key', 'value']);

            $table->string('tenant_id')->unique()->after('id');
            $table->json('data')->nullable()->after('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('tenant_configurations', function (Blueprint $table) {
            $table->dropUnique(['tenant_id']);
            $table->dropColumn(['tenant_id', 'data']);

            $table->string('key')->unique();
            $table->text('value')->nullable();
        });

        // Restaurar nombre original
        Schema::rename('tenant_configurations', 'configurations');
    }
};
