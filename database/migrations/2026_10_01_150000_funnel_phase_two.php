<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 2 do funil — etapas coerentes:
 * - converted_proposals.v3_vehicle_id: a viatura (do cliente) criada a
 *   partir da cotação convertida;
 * - converted_proposals.angariador_commission: comissão do angariador
 *   fixada na conversão (antes lia o valor atual do perfil, e mudá-lo
 *   alterava comissões passadas). As existentes ficam com o valor de hoje;
 * - pedidos (form_proposals) "convertido" passam a querer dizer "tem uma
 *   cotação aceite": os que não têm voltam a "em_analise", e os que têm
 *   ficam "convertido".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('converted_proposals', function (Blueprint $table) {
            $table->foreignId('v3_vehicle_id')->nullable()->after('proposal_id')->constrained('v3_vehicles')->nullOnDelete();
            $table->decimal('angariador_commission', 10, 2)->nullable()->after('owner_id');
        });

        DB::table('converted_proposals')
            ->whereNotNull('owner_id')
            ->whereNull('angariador_commission')
            ->orderBy('id')
            ->get(['id', 'owner_id'])
            ->each(function ($row) {
                $value = DB::table('users')->where('id', $row->owner_id)->value('commission_fixed_value');
                DB::table('converted_proposals')->where('id', $row->id)->update(['angariador_commission' => $value]);
            });

        // Cotações aceites (com cotação convertida) e os pedidos a que pertencem:
        // o pedido aponta para a cotação (form_proposals.proposal_id) ou a
        // cotação nasceu de uma oportunidade do pedido.
        $acceptedProposalIds = DB::table('converted_proposals')->whereNotNull('proposal_id')->pluck('proposal_id');
        $acceptedRequestIds = DB::table('form_proposals')->whereIn('proposal_id', $acceptedProposalIds)->pluck('id')
            ->merge(DB::table('import_opportunities')->whereIn('proposal_id', $acceptedProposalIds)->pluck('form_proposal_id'))
            ->unique()
            ->values();

        DB::table('form_proposals')
            ->where('status', 'convertido')
            ->whereNotIn('id', $acceptedRequestIds)
            ->update(['status' => 'em_analise']);

        DB::table('form_proposals')
            ->whereIn('id', $acceptedRequestIds)
            ->whereNotIn('status', ['convertido', 'arquivado'])
            ->update(['status' => 'convertido']);
    }

    public function down(): void
    {
        Schema::table('converted_proposals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('v3_vehicle_id');
            $table->dropColumn('angariador_commission');
        });
    }
};
