<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_attributes', function (Blueprint $table) {
            $table->string('alemao')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_attributes', function (Blueprint $table) {
            $table->dropColumn('alemao');
        });
    }
};