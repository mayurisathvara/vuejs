<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('plans');
            $table->string('billing_cycle')->default('trial'); // trial | monthly | yearly
            $table->unsignedInteger('sim_limit');              // purchased SIM quantity for this organization
            $table->date('start_date');
            $table->date('end_date')->nullable();              // null = forever (paid plans)
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->text('notes')->nullable();                 // admin notes
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_subscriptions');
    }
};
