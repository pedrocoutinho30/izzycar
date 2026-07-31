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
        Schema::table('legalizations', function (Blueprint $table) {
            $table->boolean('regime_especial_isv')->default(false)->after('notas');
            $table->string('invoice_path')->nullable()->after('regime_especial_isv');
        });
    }

    public function down(): void
    {
        Schema::table('legalizations', function (Blueprint $table) {
            $table->dropColumn(['regime_especial_isv', 'invoice_path']);
        });
    }
};
