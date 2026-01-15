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
        Schema::create('organization_settings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('organization_id');

            // Callback window in HOURS (24 / 48 / 72)
            $table->unsignedSmallInteger('callback_window_hours')->default(48);

            // Date format preference
            $table->string('date_formate', 20)->default('Y-m-d');

            // Enable / disable manager role
            $table->boolean('enable_manager_role')->default(false);

            // Working hours (JSON for future flexibility)
			$table->boolean('enable_working_hours')->default(false);
            $table->json('working_hours')->nullable();
            // Example:
            // { "start": "09:00", "end": "18:00" }

            $table->timestamps();

            // Relationships & constraints
            $table->foreign('organization_id')
                  ->references('id')
                  ->on('organizations')
                  ->onDelete('cascade');

            // One-to-one: one settings row per organization
            $table->unique('organization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_settings');
    }
};
