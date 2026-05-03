<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_subscriptions', function (Blueprint $table) {
            if (! Schema::hasColumn('organization_subscriptions', 'plan_name')) {
                $table->string('plan_name')->nullable()->after('plan_id');
            }

            if (! Schema::hasColumn('organization_subscriptions', 'price_per_sim')) {
                $table->decimal('price_per_sim', 10, 2)->default(0)->after('plan_name');
            }
        });

        DB::statement("
            UPDATE organization_subscriptions os
            INNER JOIN plans p ON p.id = os.plan_id
            SET
                os.plan_name = CASE
                    WHEN os.plan_name IS NULL OR os.plan_name = '' THEN p.display_name
                    ELSE os.plan_name
                END,
                os.price_per_sim = CASE
                    WHEN os.price_per_sim IS NULL OR (os.price_per_sim = 0 AND p.price_per_sim > 0) THEN p.price_per_sim
                    ELSE os.price_per_sim
                END,
                os.sim_quantity = CASE
                    WHEN os.sim_quantity IS NULL OR os.sim_quantity = 0 THEN os.sim_limit
                    ELSE os.sim_quantity
                END,
                os.features = CASE
                    WHEN os.features IS NULL THEN p.features
                    ELSE os.features
                END,
                os.total_amount = CASE
                    WHEN os.total_amount IS NULL THEN p.price_per_sim * (
                        CASE
                            WHEN os.sim_quantity IS NULL OR os.sim_quantity = 0 THEN os.sim_limit
                            ELSE os.sim_quantity
                        END
                    )
                    ELSE os.total_amount
                END
        ");
    }

    public function down(): void
    {
        Schema::table('organization_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('organization_subscriptions', 'price_per_sim')) {
                $table->dropColumn('price_per_sim');
            }

            if (Schema::hasColumn('organization_subscriptions', 'plan_name')) {
                $table->dropColumn('plan_name');
            }
        });
    }
};
