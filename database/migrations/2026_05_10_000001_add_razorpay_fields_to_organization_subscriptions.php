<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_subscriptions', function (Blueprint $table) {
            if (! Schema::hasColumn('organization_subscriptions', 'amount')) {
                $table->decimal('amount', 10, 2)->nullable()->after('total_amount');
            }

            if (! Schema::hasColumn('organization_subscriptions', 'currency')) {
                $table->string('currency', 3)->default('INR')->after('amount');
            }

            if (! Schema::hasColumn('organization_subscriptions', 'razorpay_order_id')) {
                $table->string('razorpay_order_id')->nullable()->unique()->after('currency');
            }

            if (! Schema::hasColumn('organization_subscriptions', 'razorpay_payment_id')) {
                $table->string('razorpay_payment_id')->nullable()->unique()->after('razorpay_order_id');
            }

            if (! Schema::hasColumn('organization_subscriptions', 'razorpay_signature')) {
                $table->string('razorpay_signature')->nullable()->after('razorpay_payment_id');
            }

            if (! Schema::hasColumn('organization_subscriptions', 'payment_status')) {
                $table->string('payment_status')->default('unpaid')->after('razorpay_signature');
            }

            if (! Schema::hasColumn('organization_subscriptions', 'transaction_payload')) {
                $table->json('transaction_payload')->nullable()->after('payment_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('organization_subscriptions', function (Blueprint $table) {
            $columns = [
                'transaction_payload',
                'payment_status',
                'razorpay_signature',
                'razorpay_payment_id',
                'razorpay_order_id',
                'currency',
                'amount',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('organization_subscriptions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
