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
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->string('caller_id')->nullable();
            $table->timestamp('date_time')->nullable();
            $table->string('call_status')->nullable();
            $table->string('caller_number')->nullable();
            $table->string('call_type')->nullable();
            $table->integer('caller_duration')->nullable()->comment('Duration in seconds');
            $table->integer('conversation_duration')->nullable()->comment('Duration in seconds');
            $table->integer('ring_duration')->nullable()->comment('Duration in seconds');
            $table->string('contact_status')->nullable();
            $table->string('name')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Add foreign key constraint
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('set null');
            
            // Add indexes for better performance
            $table->index(['organization_id', 'date_time']);
            $table->index('caller_number');
            $table->index('call_status');
            $table->index('date_time');
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
