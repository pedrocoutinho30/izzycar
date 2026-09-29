<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cotação criada a partir de uma Oportunidade (uma por oportunidade). Se a
 * cotação for eliminada, a oportunidade fica apenas sem ligação.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('import_opportunities', function (Blueprint $table) {
            $table->foreignId('proposal_id')->nullable()->after('form_proposal_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('import_opportunities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('proposal_id');
        });
    }
};
