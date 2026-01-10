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
        Schema::table('conversations', function (Blueprint $table) {
            // Drop existing index and column
            $table->dropIndex('conversations_type_tenant_id_index');
            $table->dropColumn('tenant_id');
        });

        Schema::table('conversations', function (Blueprint $table) {
            // Add it back as string to match Tenant model primary key
            $table->string('tenant_id')->nullable()->after('type');

            // Recreate the index
            $table->index(['type', 'tenant_id'], 'conversations_type_tenant_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex('conversations_type_tenant_id_index');
            $table->dropColumn('tenant_id');
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('type');
            $table->index(['type', 'tenant_id'], 'conversations_type_tenant_id_index');
        });
    }
};
