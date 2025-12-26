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
        // Optimize Users table indexes
        Schema::table('users', function (Blueprint $table) {
            // Composite index for role-based queries
            $table->index(['role', 'organization_id'], 'idx_users_role_org');
            
            // Composite index for organization and department filtering
            $table->index(['organization_id', 'department_id'], 'idx_users_org_dept');
            
            // Index for status filtering
            $table->index('status', 'idx_users_status');
            
            // Composite index for search queries (name, email, mobile are already individually searchable)
            $table->index(['organization_id', 'status'], 'idx_users_org_status');
        });

        // Optimize Organizations table indexes
        Schema::table('organizations', function (Blueprint $table) {
            // Index for email lookups (if not already unique indexed)
            if (!Schema::hasIndex('organizations', ['email'])) {
                $table->index('email', 'idx_organizations_email');
            }
            
            // Index for status filtering
            $table->index('status', 'idx_organizations_status');
            
            // Index for name searching
            $table->index('name', 'idx_organizations_name');
        });

        // Optimize Departments table indexes
        Schema::table('departments', function (Blueprint $table) {
            // Composite index for organization filtering (most common query)
            $table->index(['organization_id', 'created_at'], 'idx_departments_org_created');
            
            // Index for name searching
            $table->index('name', 'idx_departments_name');
        });

        // Optimize SIMs table indexes (additional to existing ones)
        Schema::table('sims', function (Blueprint $table) {
            // Composite index for organization and department filtering
            $table->index(['organization_id', 'department_id'], 'idx_sims_org_dept');
            
            // Composite index for organization and created_at (for ordering)
            $table->index(['organization_id', 'created_at'], 'idx_sims_org_created');
            
            // Index for name searching
            $table->index('name', 'idx_sims_name');
            
            // Composite index for department and created_at
            $table->index(['department_id', 'created_at'], 'idx_sims_dept_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop Users table indexes
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role_org');
            $table->dropIndex('idx_users_org_dept');
            $table->dropIndex('idx_users_status');
            $table->dropIndex('idx_users_org_status');
        });

        // Drop Organizations table indexes
        Schema::table('organizations', function (Blueprint $table) {
            if (Schema::hasIndex('organizations', 'idx_organizations_email')) {
                $table->dropIndex('idx_organizations_email');
            }
            $table->dropIndex('idx_organizations_status');
            $table->dropIndex('idx_organizations_name');
        });

        // Drop Departments table indexes
        Schema::table('departments', function (Blueprint $table) {
            $table->dropIndex('idx_departments_org_created');
            $table->dropIndex('idx_departments_name');
        });

        // Drop SIMs table indexes
        Schema::table('sims', function (Blueprint $table) {
            $table->dropIndex('idx_sims_org_dept');
            $table->dropIndex('idx_sims_org_created');
            $table->dropIndex('idx_sims_name');
            $table->dropIndex('idx_sims_dept_created');
        });
    }
};
