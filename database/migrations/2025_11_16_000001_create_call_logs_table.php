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
        Schema::create('call_logs', function (Blueprint $table) {
            $table->id();

            // Unique call identifier
            $table->string('unique_id')->unique();

            // Organization
            $table->unsignedBigInteger('organization_id')->nullable();

            // Caller info
            $table->string('caller_id', 15)->nullable()->index();
            $table->string('caller_number')->nullable()->index();

            // Date & time
            $table->date('date')->nullable()->index();
            $table->time('time')->nullable();
            $table->timestamp('date_time')->nullable()->index();

            // Call details
            $table->string('call_type')->nullable();
            $table->string('call_status')->nullable()->index();

            // Durations
            $table->time('caller_duration')->nullable();
            $table->time('conversation_duration')->nullable();
            $table->time('ring_duration')->nullable();

            // Callback info
            $table->enum('call_back', ['Y', 'N'])->default('N')->index();
            $table->string('callback_id', 255)->nullable();

            // Meta info
            $table->string('contact_status')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('hangup_by')->nullable();
            $table->string('name')->nullable();
            $table->string('team_name')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign key
            $table->foreign('organization_id')
                  ->references('id')
                  ->on('organizations')
                  ->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_logs');
    }
};
