<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * proposals.fuel era um ENUM fechado e rejeitava "Gasolina (HEV)" e
     * "Diesel (HEV)". Passa a string; os valores permitidos ficam validados
     * nos controllers. Os valores existentes mantêm-se.
     */
    public function up(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->string('fuel', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        // Sem reversão: voltar ao ENUM falharia com cotações HEV já guardadas.
    }
};
