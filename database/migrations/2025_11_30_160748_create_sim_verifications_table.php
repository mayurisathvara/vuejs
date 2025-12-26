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
		Schema::create('sim_verifications', function (Blueprint $table) {
			$table->id();
			$table->string('verification_id')->unique();
			$table->string('mobile');
			$table->string('token_hash');
			$table->string('status')->default('pending'); // pending, verified
			$table->string('sender_number')->nullable();
			$table->timestamp('expires_at');
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::dropIfExists('sim_verifications');
	}

};
