<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('call_logs', function (Blueprint $table) {
            $table->string('phone_code', 5)->default('91')->after('caller_number');
        });
    }

    public function down()
    {
        Schema::table('call_logs', function (Blueprint $table) {
            $table->dropColumn('phone_code');
        });
    }
};