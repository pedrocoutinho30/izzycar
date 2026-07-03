<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('legalization_id')->nullable()->after('v3_vehicle_id');
            $table->foreign('legalization_id')->references('id')->on('legalizations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['legalization_id']);
            $table->dropColumn('legalization_id');
        });
    }
};
