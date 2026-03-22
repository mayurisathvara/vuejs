<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Extend the sims.status enum to include the new system-controlled value.
        // Using raw SQL because Laravel's Blueprint::change() does not reliably
        // modify ENUM columns on all MySQL versions.
        DB::statement(
            "ALTER TABLE sims MODIFY COLUMN status ENUM('active','inactive','inactive_plan_limit') NOT NULL DEFAULT 'active'"
        );
    }

    public function down(): void
    {
        // First reset any inactive_plan_limit rows so they don't violate the old enum
        DB::table('sims')
            ->where('status', 'inactive_plan_limit')
            ->update(['status' => 'inactive']);

        DB::statement(
            "ALTER TABLE sims MODIFY COLUMN status ENUM('active','inactive') NOT NULL DEFAULT 'active'"
        );
    }
};
