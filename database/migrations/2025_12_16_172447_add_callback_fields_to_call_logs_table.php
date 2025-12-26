<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('call_logs', function (Blueprint $table) {
			
			$table->string('caller_id', 15)
                ->nullable()
                ->after('user_id');
				  
            $table->enum('call_back', ['Y', 'N'])
                  ->default('N'); // change position if needed

            $table->string('callback_id', 255)
                  ->nullable()
                  ->after('call_back');
        });
    }

    public function down(): void
    {
        Schema::table('call_logs', function (Blueprint $table) {
            $table->dropColumn(['call_back', 'callback_id']);
        });
    }
};
