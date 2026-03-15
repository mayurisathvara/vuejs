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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id')->nullable()->after('organization_id');
            
            // Foreign key constraint
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('set null');
            
            // Index for better performance
            $table->index('team_id', 'users_team_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropIndex('users_team_index');
            $table->dropColumn('team_id');
        });
    }
};
