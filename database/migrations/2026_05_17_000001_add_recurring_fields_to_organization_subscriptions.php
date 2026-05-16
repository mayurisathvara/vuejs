<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_subscriptions', function (Blueprint $table) {
            $table->boolean('auto_renew')->default(true)->after('status');
            $table->string('razorpay_subscription_id', 100)->nullable()->unique()->after('razorpay_signature');
            $table->timestamp('auto_renew_failed_at')->nullable()->after('razorpay_subscription_id');
            $table->string('auto_renew_failure_reason', 500)->nullable()->after('auto_renew_failed_at');
        });
    }

    public function down(): void
    {
        Schema::table('organization_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['auto_renew', 'razorpay_subscription_id', 'auto_renew_failed_at', 'auto_renew_failure_reason']);
        });
    }
};
