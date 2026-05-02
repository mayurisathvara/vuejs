<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');                  // free_trial | basic | advance (can repeat per billing type)
            $table->string('display_name');           // Free Trial | Basic | Advance
            $table->string('billing_type');           // trial | monthly | yearly
            $table->decimal('price_per_sim', 8, 2)->default(0.00);
            $table->unsignedSmallInteger('trial_days')->nullable(); // only for trial plan
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default plans
        DB::table('plans')->insert([
            [
                'name'          => 'free_trial',
                'display_name'  => 'Free Trial',
                'billing_type'  => 'trial',
                'price_per_sim' => 0.00,
                'trial_days'    => 14,
                'features'      => json_encode(['call_tracking', 'reports']),
                'is_active'     => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'name'          => 'basic',
                'display_name'  => 'Basic',
                'billing_type'  => 'monthly',
                'price_per_sim' => 150.00,
                'trial_days'    => null,
                'features'      => json_encode(['call_tracking', 'reports']),
                'is_active'     => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'name'          => 'advance',
                'display_name'  => 'Advance',
                'billing_type'  => 'monthly',
                'price_per_sim' => 200.00,
                'trial_days'    => null,
                'features'      => json_encode(['call_tracking', 'reports', 'ai', 'api']),
                'is_active'     => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'name'          => 'basic',
                'display_name'  => 'Basic',
                'billing_type'  => 'yearly',
                'price_per_sim' => 140.00,
                'trial_days'    => null,
                'features'      => json_encode(['call_tracking', 'reports']),
                'is_active'     => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'name'          => 'advance',
                'display_name'  => 'Advance',
                'billing_type'  => 'yearly',
                'price_per_sim' => 185.00,
                'trial_days'    => null,
                'features'      => json_encode(['call_tracking', 'reports', 'ai', 'api']),
                'is_active'     => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
