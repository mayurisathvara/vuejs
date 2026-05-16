<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // All users that existed before email verification was introduced are
        // considered already verified so the new requirement only applies to
        // accounts registered after this migration runs.
        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        // Non-reversible data migration.
    }
};
