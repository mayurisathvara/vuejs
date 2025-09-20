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
            $table->unsignedBigInteger('department_id')->nullable()->after('organization_id');
            
            // Foreign key constraint
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            
            // Index for better performance
            $table->index('department_id', 'users_department_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropIndex('users_department_index');
            $table->dropColumn('department_id');
        });
    }
};
