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
        Schema::table('sales', function (Blueprint $table) {
            $table->boolean('has_trade_in')->default(false);
            $table->integer('trade_in_vehicle_id')->nullable()->after('has_trade_in');
            $table->decimal('trade_in_value', 10, 2)->nullable()->after('trade_in_vehicle_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'has_trade_in',
                'trade_in_vehicle_id',
                'trade_in_value',
            ]);
        });
    }
};
