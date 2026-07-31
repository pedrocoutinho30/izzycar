<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Os documentos carregados antes da renomeação para siglas oficiais (CMATR, CCEUR, etc.)
 * ficaram com o 'tipo' antigo na BD, deixando de corresponder a nenhuma sigla atual
 * (ex.: 'dua' já não existe em Legalization::DOCUMENTOS, agora é 'CMATR').
 * Esta migration atualiza esses registos para as siglas correspondentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        $map = [
            'dua'            => 'CMATR',
            'coc'            => 'CCEUR',
            'modelo9'        => 'DHTEC',
            'modelo112'      => 'CINSP',
            'cartao_cidadao' => 'CCIDA',
            'fatura_compra'  => 'FATDV',
            'autorizacao'    => 'PRODH',
        ];

        foreach ($map as $old => $new) {
            DB::table('legalization_documents')->where('tipo', $old)->update(['tipo' => $new]);
        }
    }

    public function down(): void
    {
        $map = [
            'CMATR' => 'dua',
            'CCEUR' => 'coc',
            'DHTEC' => 'modelo9',
            'CINSP' => 'modelo112',
            'CCIDA' => 'cartao_cidadao',
            'FATDV' => 'fatura_compra',
            'PRODH' => 'autorizacao',
        ];

        foreach ($map as $new => $old) {
            DB::table('legalization_documents')->where('tipo', $new)->update(['tipo' => $old]);
        }
    }
};
