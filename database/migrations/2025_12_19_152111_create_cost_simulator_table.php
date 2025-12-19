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
        Schema::create('cost_simulators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id');
            $table->decimal('car_value', 10, 2);
            $table->decimal('commission_cost', 10, 2);
            $table->decimal('inspection_commission_cost', 10, 2);
            $table->decimal('transport', 10, 2);
            $table->decimal('ipo_cost', 10, 2);
            $table->decimal('imt_cost', 10, 2);
            $table->decimal('registration_cost', 10, 2);
            $table->decimal('plates_cost', 10, 2);
            $table->decimal('total_cost', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_simulators');
    }
};
