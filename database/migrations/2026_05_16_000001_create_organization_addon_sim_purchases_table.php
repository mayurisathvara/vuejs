<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_addon_sim_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained('organization_subscriptions')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans');
            $table->integer('sim_quantity');
            $table->decimal('price_per_sim', 8, 2);
            $table->integer('billing_cycle_days');
            $table->integer('remaining_days');
            $table->decimal('proration_factor', 10, 6);
            $table->decimal('prorated_amount', 10, 2);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('INR');
            $table->string('razorpay_order_id')->nullable()->unique();
            $table->string('razorpay_payment_id')->nullable()->unique();
            $table->string('razorpay_signature')->nullable();
            $table->string('payment_status', 20)->default('unpaid'); // unpaid | paid | failed
            $table->boolean('merged_at_renewal')->default(false);
            $table->json('transaction_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_addon_sim_purchases');
    }
};
