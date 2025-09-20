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
        // Add indexes for users table search performance
        Schema::table('users', function (Blueprint $table) {
            $table->index(['name', 'email', 'mobile'], 'users_search_index');
            $table->index('organization_id', 'users_organization_index');
            $table->index('role', 'users_role_index');
            $table->index('status', 'users_status_index');
        });

        // Add indexes for organizations table search performance
        Schema::table('organizations', function (Blueprint $table) {
            $table->index(['name', 'email', 'mobile'], 'organizations_search_index');
            $table->index('status', 'organizations_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_search_index');
            $table->dropIndex('users_organization_index');
            $table->dropIndex('users_role_index');
            $table->dropIndex('users_status_index');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropIndex('organizations_search_index');
            $table->dropIndex('organizations_status_index');
        });
    }
};
