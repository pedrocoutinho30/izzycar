<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('converted_proposals', function (Blueprint $table) {
            $table->boolean('veiculo_entregue')->default(false)->after('valor_comissao_final');
            $table->date('veiculo_entregue_em')->nullable()->after('veiculo_entregue');
        });
    }

    public function down(): void
    {
        Schema::table('converted_proposals', function (Blueprint $table) {
            $table->dropColumn(['veiculo_entregue', 'veiculo_entregue_em']);
        });
    }
};
