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
        Schema::create('status_proposal_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('converted_proposal_id');
            $table->string('old_status');
            $table->string('new_status');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_proposal_history');
    }
};
