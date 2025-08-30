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
        Schema::table('ad_listings', function (Blueprint $table) {
            Schema::table('ad_listings', function (Blueprint $table) {
                $table->boolean('active')->default(true)->after('url');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ad_listings', function (Blueprint $table) {
            //
        });
    }
};
