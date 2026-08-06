<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('converted_proposals', function (Blueprint $table) {
            $table->string('comprovativo_pagamento')->nullable()->after('comissao_paga_em');
        });
    }

    public function down(): void
    {
        Schema::table('converted_proposals', function (Blueprint $table) {
            $table->dropColumn('comprovativo_pagamento');
        });
    }
};
