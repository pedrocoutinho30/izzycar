<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Código aleatório do acompanhamento público do processo. Substitui o id
 * sequencial no URL (/proposta/.../timeline), que permitia ver processos de
 * outros clientes mudando o número. As cotações existentes recebem um código.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('converted_proposals', function (Blueprint $table) {
            $table->string('tracking_code', 32)->nullable();
            $table->unique('tracking_code', 'cp_tracking_code_unique');
        });

        DB::table('converted_proposals')->whereNull('tracking_code')->orderBy('id')->each(function ($row) {
            DB::table('converted_proposals')->where('id', $row->id)->update(['tracking_code' => Str::random(24)]);
        });
    }

    public function down(): void
    {
        Schema::table('converted_proposals', function (Blueprint $table) {
            $table->dropUnique('cp_tracking_code_unique');
            $table->dropColumn('tracking_code');
        });
    }
};
