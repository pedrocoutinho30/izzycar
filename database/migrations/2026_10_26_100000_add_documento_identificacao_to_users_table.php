<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Documento de identificação (frente e verso) enviado na candidatura a
 * angariador. Ficam em disco privado; só o admin os consulta.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('documento_frente_path')->nullable();
            $table->string('documento_verso_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['documento_frente_path', 'documento_verso_path']);
        });
    }
};
