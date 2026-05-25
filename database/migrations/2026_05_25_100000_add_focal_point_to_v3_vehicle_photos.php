<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('v3_vehicle_photos', function (Blueprint $table) {
            $table->decimal('focal_x', 5, 2)->default(50)->after('is_cover');
            $table->decimal('focal_y', 5, 2)->default(50)->after('focal_x');
        });
    }

    public function down(): void
    {
        Schema::table('v3_vehicle_photos', function (Blueprint $table) {
            $table->dropColumn(['focal_x', 'focal_y']);
        });
    }
};
