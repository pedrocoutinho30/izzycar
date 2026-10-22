<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * VIN (número de chassis) do veículo da cotação convertida, preenchido no
 * backoffice. Aparece no contrato de prestação de serviços quando existe.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('converted_proposals', function (Blueprint $table) {
            $table->string('vin', 17)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('converted_proposals', function (Blueprint $table) {
            $table->dropColumn('vin');
        });
    }
};
