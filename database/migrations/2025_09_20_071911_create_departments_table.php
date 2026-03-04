<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255);
            $table->unsignedBigInteger('organization_id');
            $table->timestamps(); // created_at & updated_at
            
            // Foreign key constraint
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            
            // Indexes for better performance
            $table->index('organization_id', 'teams_organization_index');
            $table->index('name', 'teams_name_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
