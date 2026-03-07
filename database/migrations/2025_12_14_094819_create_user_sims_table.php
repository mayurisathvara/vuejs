<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('user_sims', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('sim_id')
                  ->constrained('sims')
                  ->cascadeOnDelete();
				  
		    $table->string('mobile', 15)->nullable();

            $table->timestamps();

            // Prevent duplicate SIM assignment
            $table->unique(['user_id', 'sim_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_sims');
    }
};
