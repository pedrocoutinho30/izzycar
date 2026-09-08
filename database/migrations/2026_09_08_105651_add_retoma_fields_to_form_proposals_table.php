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
        Schema::table('form_proposals', function (Blueprint $table) {
            $table->string('retoma_option')->nullable()->after('extras');
            $table->string('retoma_brand')->nullable()->after('retoma_option');
            $table->string('retoma_model')->nullable()->after('retoma_brand');
            $table->integer('retoma_year')->nullable()->after('retoma_model');
            $table->integer('retoma_km')->nullable()->after('retoma_year');
            $table->string('retoma_fuel')->nullable()->after('retoma_km');
            $table->text('retoma_info')->nullable()->after('retoma_fuel');
            $table->json('retoma_photos')->nullable()->after('retoma_info');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_proposals', function (Blueprint $table) {
            $table->dropColumn([
                'retoma_option',
                'retoma_brand',
                'retoma_model',
                'retoma_year',
                'retoma_km',
                'retoma_fuel',
                'retoma_info',
                'retoma_photos',
            ]);
        });
    }
};
