<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('radar_equipment_aliases', function (Blueprint $table) {
            // Guardado à parte do label do item canónico (que pode ter sido
            // renomeado/fundido) para se conseguir sugerir um nome sensato quando o
            // utilizador desacopla este alias de volta para um item próprio - ver
            // RadarEquipmentController::detachAlias().
            $table->string('raw_label')->nullable()->after('raw_key');
        });
    }

    public function down(): void
    {
        Schema::table('radar_equipment_aliases', function (Blueprint $table) {
            $table->dropColumn('raw_label');
        });
    }
};
