<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('v3_vehicles', function (Blueprint $table) {
            $table->text('ad_text')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('v3_vehicles', function (Blueprint $table) {
            $table->dropColumn('ad_text');
        });
    }
};
