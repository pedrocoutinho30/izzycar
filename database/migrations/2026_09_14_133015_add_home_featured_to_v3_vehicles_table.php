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
        Schema::table('v3_vehicles', function (Blueprint $table) {
            $table->boolean('home_featured')->default(false)->after('show_online');
            $table->unsignedInteger('home_featured_order')->nullable()->after('home_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v3_vehicles', function (Blueprint $table) {
            $table->dropColumn(['home_featured', 'home_featured_order']);
        });
    }
};
