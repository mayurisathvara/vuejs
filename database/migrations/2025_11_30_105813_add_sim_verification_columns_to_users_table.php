<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
	{
		Schema::table('users', function (Blueprint $table) {
			$table->string('sim_verified_mobile')->nullable();
			$table->timestamp('sim_verified_at')->nullable();
		});
	}

	public function down()
	{
		Schema::table('users', function (Blueprint $table) {
			$table->dropColumn(['sim_verified_mobile', 'sim_verified_at']);
		});
	}

};
