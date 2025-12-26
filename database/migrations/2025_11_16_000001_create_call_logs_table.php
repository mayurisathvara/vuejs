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

            $table->string('unique_id')->unique();

            $table->unsignedBigInteger('organization_id')->nullable();

            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->timestamp('date_time')->nullable();

            $table->string('call_type')->nullable();
            $table->string('call_status')->nullable();
            $table->string('caller_number')->nullable();

            $table->time('caller_duration')->nullable();
            $table->time('conversation_duration')->nullable();
            $table->time('ring_duration')->nullable();

            $table->string('contact_status')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('hangup_by')->nullable();
            $table->string('name')->nullable();
            $table->string('department_name')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign key
            $table->foreign('organization_id')
                  ->references('id')
                  ->on('organizations')
                  ->onDelete('set null');

            // Indexes for performance
            $table->index(['organization_id', 'date_time']);
            $table->index('caller_number');
            $table->index('call_status');
            $table->index('date_time');
            $table->index('date');
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
