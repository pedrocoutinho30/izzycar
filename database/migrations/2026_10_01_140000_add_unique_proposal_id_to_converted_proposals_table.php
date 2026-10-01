<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Uma cotação só pode dar origem a uma cotação convertida — o índice único
 * garante-o mesmo com dois pedidos de aceitação em simultâneo. (Várias
 * cotações convertidas sem cotação, proposal_id NULL, continuam permitidas.)
 */
return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('converted_proposals')
            ->whereNotNull('proposal_id')
            ->select('proposal_id')
            ->groupBy('proposal_id')
            ->havingRaw('count(*) > 1')
            ->pluck('proposal_id');

        if ($duplicates->isNotEmpty()) {
            throw new RuntimeException('Há cotações aceites mais do que uma vez (proposal_id: ' . $duplicates->implode(', ') . '). Resolva os duplicados antes de criar o índice único.');
        }

        Schema::table('converted_proposals', function (Blueprint $table) {
            $table->unique('proposal_id');
        });
    }

    public function down(): void
    {
        Schema::table('converted_proposals', function (Blueprint $table) {
            $table->dropUnique(['proposal_id']);
        });
    }
};
