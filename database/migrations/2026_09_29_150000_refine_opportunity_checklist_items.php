<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Revisão dos itens das checklists das Oportunidades:
 * - "Cabos de carregamento" passa para a checklist de elétricos;
 * - itens redundantes são fundidos num só (o estado já marcado passa para o
 *   item que fica, se esse ainda não tiver estado nessa oportunidade);
 * - "Heat Pump", "Histórico de carregamento" e "Preço bruto confirmado" saem.
 */
return new class extends Migration
{
    /** Item removido => item que o substitui (mesma oportunidade). */
    private const MERGES = [
        'base.livro_revisoes' => 'base.historico_manutencao',
        'base.certificado_soh' => 'electric.teste_bateria',
        'electric.soh' => 'electric.teste_bateria',
        'electric.capacidade_bateria' => 'electric.teste_bateria',
        'electric.garantia_veiculo' => 'base.garantia',
    ];

    private const REMOVALS = ['electric.heat_pump', 'electric.historico_carregamento', 'base.preco_bruto_confirmado'];

    private const LABELS = [
        'base.historico_manutencao' => 'Livro de revisões / Histórico de manutenção',
        'base.garantia' => 'Garantia do veículo',
        'electric.teste_bateria' => 'Certificado SOH / teste da bateria',
    ];

    private const ORDER = [
        'base' => ['vin', 'coc', 'historico_manutencao', 'garantia', 'historico_acidentes', 'segunda_chave', 'manual', 'documentos_matricula', 'documentos_exportacao', 'fatura'],
        'electric' => ['teste_bateria', 'quimica_bateria', 'garantia_bateria', 'cabos_carregamento', 'cabo_type2', 'cabo_ccs', 'carregador_domestico'],
    ];

    public function up(): void
    {
        DB::transaction(function () {
            $checklists = DB::table('opportunity_checklists')->pluck('id', 'slug');

            // Cabos de carregamento: só em elétricos (mantém os estados já marcados).
            DB::table('opportunity_checklist_items')
                ->where('opportunity_checklist_id', $checklists['base'])
                ->where('slug', 'cabos_carregamento')
                ->update(['opportunity_checklist_id' => $checklists['electric'], 'updated_at' => now()]);

            foreach (self::MERGES as $from => $to) {
                $fromId = $this->itemId($from);
                $toId = $this->itemId($to);

                if (! $fromId || ! $toId) {
                    continue;
                }

                $alreadySet = DB::table('import_opportunity_checklist_entries')
                    ->where('opportunity_checklist_item_id', $toId)
                    ->pluck('import_opportunity_id');

                DB::table('import_opportunity_checklist_entries')
                    ->where('opportunity_checklist_item_id', $fromId)
                    ->whereNotIn('import_opportunity_id', $alreadySet)
                    ->update(['opportunity_checklist_item_id' => $toId]);

                // Os restantes registos do item removido saem por cascade.
                DB::table('opportunity_checklist_items')->where('id', $fromId)->delete();
            }

            foreach (self::REMOVALS as $key) {
                if ($id = $this->itemId($key)) {
                    DB::table('opportunity_checklist_items')->where('id', $id)->delete();
                }
            }

            foreach (self::LABELS as $key => $label) {
                DB::table('opportunity_checklist_items')->where('id', $this->itemId($key))->update(['label' => $label, 'updated_at' => now()]);
            }

            $this->applyOrder(self::ORDER);
        });
    }

    /**
     * Repõe os itens originais. Os estados dos itens fundidos ficam no item
     * que os absorveu (não é possível separá-los de volta).
     */
    public function down(): void
    {
        DB::transaction(function () {
            $checklists = DB::table('opportunity_checklists')->pluck('id', 'slug');

            DB::table('opportunity_checklist_items')
                ->where('opportunity_checklist_id', $checklists['electric'])
                ->where('slug', 'cabos_carregamento')
                ->update(['opportunity_checklist_id' => $checklists['base']]);

            $restore = [
                'base.livro_revisoes' => 'Livro de revisões',
                'base.certificado_soh' => 'Certificado SOH',
                'base.preco_bruto_confirmado' => 'Preço bruto confirmado',
                'electric.soh' => 'SOH',
                'electric.capacidade_bateria' => 'Capacidade da bateria',
                'electric.heat_pump' => 'Heat Pump',
                'electric.garantia_veiculo' => 'Garantia do veículo',
                'electric.historico_carregamento' => 'Histórico de carregamento',
            ];

            foreach ($restore as $key => $label) {
                [$checklist, $slug] = explode('.', $key);
                DB::table('opportunity_checklist_items')->insertOrIgnore([
                    'opportunity_checklist_id' => $checklists[$checklist],
                    'slug' => $slug,
                    'label' => $label,
                    'sort_order' => 0,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach (['base.historico_manutencao' => 'Histórico de manutenção', 'base.garantia' => 'Garantia', 'electric.teste_bateria' => 'Certificado/teste da bateria'] as $key => $label) {
                DB::table('opportunity_checklist_items')->where('id', $this->itemId($key))->update(['label' => $label]);
            }

            $this->applyOrder([
                'base' => ['vin', 'coc', 'livro_revisoes', 'historico_manutencao', 'garantia', 'certificado_soh', 'historico_acidentes', 'segunda_chave', 'cabos_carregamento', 'manual', 'documentos_matricula', 'documentos_exportacao', 'fatura', 'preco_bruto_confirmado'],
                'electric' => ['soh', 'capacidade_bateria', 'quimica_bateria', 'teste_bateria', 'cabo_type2', 'cabo_ccs', 'carregador_domestico', 'heat_pump', 'garantia_bateria', 'garantia_veiculo', 'historico_carregamento'],
            ]);
        });
    }

    private function itemId(string $key): ?int
    {
        [$checklist, $slug] = explode('.', $key);

        return DB::table('opportunity_checklist_items')
            ->join('opportunity_checklists', 'opportunity_checklists.id', '=', 'opportunity_checklist_items.opportunity_checklist_id')
            ->where('opportunity_checklists.slug', $checklist)
            ->where('opportunity_checklist_items.slug', $slug)
            ->value('opportunity_checklist_items.id');
    }

    private function applyOrder(array $order): void
    {
        foreach ($order as $checklist => $slugs) {
            foreach ($slugs as $index => $slug) {
                DB::table('opportunity_checklist_items')
                    ->where('id', $this->itemId("{$checklist}.{$slug}"))
                    ->update(['sort_order' => $index]);
            }
        }
    }
};
