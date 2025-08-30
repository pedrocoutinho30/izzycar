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
        Schema::table('ad_searches', function (Blueprint $table) {
            Schema::table('ad_searches', function (Blueprint $table) {
                $table->string('description')->default(true)->after('fuel');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ad_searches', function (Blueprint $table) {
            //
        });
    }
};
