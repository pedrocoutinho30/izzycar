<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('v3_vehicles', function (Blueprint $table) {
            $table->decimal('asking_price', 10, 2)->nullable()->after('purchase_vat_paid');
        });
    }

    public function down(): void
    {
        Schema::table('v3_vehicles', function (Blueprint $table) {
            $table->dropColumn('asking_price');
        });
    }
};
