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
        Schema::create('model_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_car_id')->constrained()->onDelete('cascade');
            $table->string('year');
            $table->timestamps();

            $table->unique(['model_car_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_years');
    }
};
