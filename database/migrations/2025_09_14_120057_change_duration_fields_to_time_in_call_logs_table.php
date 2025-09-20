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
        Schema::table('call_logs', function (Blueprint $table) {
            // Change duration fields from integer to time
            $table->time('caller_duration')->nullable()->change();
            $table->time('conversation_duration')->nullable()->change();
            $table->time('ring_duration')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('call_logs', function (Blueprint $table) {
            // Revert duration fields back to integer
            $table->integer('caller_duration')->nullable()->change();
            $table->integer('conversation_duration')->nullable()->change();
            $table->integer('ring_duration')->nullable()->change();
        });
    }
};
