<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('call_logs', function (Blueprint $table) {

            // Composite index (HIGHLY important)
            $table->index(['user_id', 'date'], 'idx_user_date');

            // Single column indexes
            $table->index('call_type', 'idx_call_type');
            $table->index('call_status', 'idx_call_status');
            $table->index('organization_id', 'idx_org_id'); // optional but useful
        });
    }

    public function down()
    {
        Schema::table('call_logs', function (Blueprint $table) {
            $table->dropIndex('idx_user_date');
            $table->dropIndex('idx_call_type');
            $table->dropIndex('idx_call_status');
            $table->dropIndex('idx_org_id');
        });
    }
};
