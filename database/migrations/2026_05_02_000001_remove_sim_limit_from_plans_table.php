<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('plans', 'sim_limit')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->dropColumn('sim_limit');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('plans', 'sim_limit')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->unsignedInteger('sim_limit')->default(10)->after('price_per_sim');
            });
        }
    }
};
