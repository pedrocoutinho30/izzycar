<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * O cliente pode pedir cotação de uma das "outras opções" mostradas na
 * cotação pública — fica registado quando, para não repetir o pedido e para
 * a equipa o ver na oportunidade.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('import_opportunities', function (Blueprint $table) {
            $table->timestamp('client_quote_requested_at')->nullable()->after('proposal_id');
        });
    }

    public function down(): void
    {
        Schema::table('import_opportunities', function (Blueprint $table) {
            $table->dropColumn('client_quote_requested_at');
        });
    }
};
