<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('organization_subscriptions', function (Blueprint $table) {

            // PLAN SNAPSHOT FIELDS
          
            $table->json('features')->nullable();
            // OPTIONAL BUT VERY USEFUL
            $table->decimal('total_amount', 10, 2)->nullable()->after('features');
            $table->integer('sim_quantity')->after('sim_limit');
        });
    }

    public function down()
    {
        Schema::table('organization_subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'features',
                'total_amount',
                'sim_quantity'
            ]);
        });
    }
};