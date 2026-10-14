<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Número da oferta/referência do anúncio no vendedor ou no portal de origem. */
    public function up(): void
    {
        Schema::table('import_opportunities', function (Blueprint $table) {
            $table->string('offer_number', 100)->nullable()->after('listing_url');
        });
    }

    public function down(): void
    {
        Schema::table('import_opportunities', function (Blueprint $table) {
            $table->dropColumn('offer_number');
        });
    }
};
