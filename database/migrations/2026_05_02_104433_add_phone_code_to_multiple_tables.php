<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // USERS TABLE
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_code', 5)->default('91')->after('mobile');
        });

        // SIMS TABLE
        Schema::table('sims', function (Blueprint $table) {
            $table->string('phone_code', 5)->default('91')->after('mobile');
        });

        // EXCLUDED NUMBERS TABLE
        Schema::table('excluded_numbers', function (Blueprint $table) {
            $table->string('phone_code', 5)->default('91')->after('phone_number');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone_code');
        });

        Schema::table('sims', function (Blueprint $table) {
            $table->dropColumn('phone_code');
        });

        Schema::table('excluded_numbers', function (Blueprint $table) {
            $table->dropColumn('phone_code');
        });
    }
};