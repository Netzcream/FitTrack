<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenant_configurations')) {
            Schema::table('tenant_configurations', function (Blueprint $table) {
                if (! Schema::hasColumn('tenant_configurations', 'tenant_id')) {
                    $table->string('tenant_id')->nullable()->after('id');
                }
                if (! Schema::hasColumn('tenant_configurations', 'data')) {
                    $table->json('data')->nullable()->after('tenant_id');
                }
            });

            $this->backfillTenantConfigurationTenantId();
            $this->ensureTenantIdUnique();
            $this->ensureTenantIdNotNull();
            return;
        }

        if (Schema::hasTable('configurations')) {
            Schema::rename('configurations', 'tenant_configurations');

            $connection = Schema::getConnection();
            $result = $connection->select(
                "SELECT COUNT(1) as cnt FROM information_schema.statistics WHERE table_schema = database() AND table_name = 'tenant_configurations' AND index_name = 'configurations_key_unique'"
            );
            $indexExists = !empty($result) && ($result[0]->cnt ?? 0) > 0;

            Schema::table('tenant_configurations', function (Blueprint $table) use ($indexExists) {
                if ($indexExists) {
                    $table->dropUnique('configurations_key_unique');
                }

                if (Schema::hasColumn('tenant_configurations', 'key')) {
                    $table->dropColumn(['key', 'value']);
                }

                if (! Schema::hasColumn('tenant_configurations', 'tenant_id')) {
                    $table->string('tenant_id')->nullable()->after('id');
                }
                if (! Schema::hasColumn('tenant_configurations', 'data')) {
                    $table->json('data')->nullable()->after('tenant_id');
                }
            });

            $this->backfillTenantConfigurationTenantId();
            $this->ensureTenantIdUnique();
            $this->ensureTenantIdNotNull();
            return;
        }

        Schema::create('tenant_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->unique();
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('tenant_configurations')) {
            return;
        }

        Schema::table('tenant_configurations', function (Blueprint $table) {
            if (Schema::hasColumn('tenant_configurations', 'tenant_id')) {
                $table->dropUnique(['tenant_id']);
                $table->dropColumn(['tenant_id', 'data']);
            }

            if (! Schema::hasColumn('tenant_configurations', 'key')) {
                $table->string('key')->unique();
                $table->text('value')->nullable();
            }
        });

        Schema::rename('tenant_configurations', 'configurations');
    }

    private function backfillTenantConfigurationTenantId(): void
    {
        $tenantId = tenancy()->initialized ? (string) tenancy()->tenant?->id : null;
        if (! $tenantId) {
            return;
        }

        DB::table('tenant_configurations')
            ->whereNull('tenant_id')
            ->orWhere('tenant_id', '')
            ->update(['tenant_id' => $tenantId]);

        $ids = DB::table('tenant_configurations')->orderByDesc('id')->pluck('id');
        if ($ids->count() > 1) {
            $keepId = $ids->shift();
            DB::table('tenant_configurations')->whereIn('id', $ids->all())->delete();
        }
    }

    private function ensureTenantIdUnique(): void
    {
        $connection = Schema::getConnection();
        $result = $connection->select(
            "SELECT COUNT(1) as cnt FROM information_schema.statistics WHERE table_schema = database() AND table_name = 'tenant_configurations' AND index_name = 'tenant_configurations_tenant_id_unique'"
        );
        $indexExists = !empty($result) && ($result[0]->cnt ?? 0) > 0;
        if (! $indexExists) {
            Schema::table('tenant_configurations', function (Blueprint $table) {
                $table->unique('tenant_id');
            });
        }
    }

    private function ensureTenantIdNotNull(): void
    {
        Schema::table('tenant_configurations', function (Blueprint $table) {
            $table->string('tenant_id')->nullable(false)->change();
        });
    }
};
