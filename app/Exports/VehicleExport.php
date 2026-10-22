<?php

namespace App\Exports;

use App\Models\Legalization;
use App\Models\V3Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Exportação de viaturas V3. Não inclui custos, compra, despesas, vendas nem
 * margens (só o preço de anúncio), notas internas, texto do anúncio, destaques
 * da home, caminhos de fotos/documentos, ids técnicos nem NIF/identificação de
 * clientes, fornecedores ou vendedores (só o nome).
 */
class VehicleExport extends ExportDefinition
{
    public function type(): string
    {
        return 'vehicles';
    }

    public function filenamePrefix(): string
    {
        return 'viaturas';
    }

    public function title(): string
    {
        return 'Viaturas';
    }

    public function filterKeys(): array
    {
        return ['search', 'status', 'fuel'];
    }

    protected function baseQuery(array $filters): Builder
    {
        // Mesmo scope da listagem (V3VehicleController::index)
        return V3Vehicle::query()->filtrar($filters);
    }

    /**
     * Ordem da listagem (created_at desc); o id desempata para a paginação por
     * offset do lazy() ser determinista (lazyById ordenaria só por id).
     */
    public function rows(Builder $query, int $chunk): iterable
    {
        return $query->orderByDesc('created_at')->orderByDesc('id')->lazy($chunk);
    }

    protected function defineFields(): array
    {
        $i = 'Identificação';
        $t = 'Técnico';
        $s = 'Estado e site';
        $l = 'Ligações';
        $o = 'Outros';

        return [
            ExportField::make('reference', 'Referência', $i, default: true, width: 16),
            ExportField::make('brand', 'Marca', $i, default: true),
            ExportField::make('model', 'Modelo', $i, default: true, width: 22),
            ExportField::make('sub_model', 'Submodelo', $i, width: 22),
            ExportField::make('version', 'Versão', $i, default: true, width: 28),
            // "year" em bruto tem significado por confirmar: sai a etiqueta (2022, 03/2022 ou 15/03/2022).
            ExportField::make('year_label', 'Ano', $i, reader: fn (Model $v) => $v->year ? $v->year_label : null, default: true, width: 12),
            ExportField::make('registration', 'Matrícula', $i, default: true, width: 14),
            ExportField::make('vin', 'VIN', $i, width: 22),

            ExportField::make('fuel', 'Combustível', $t, default: true, width: 18),
            ExportField::make('kilometers', 'Quilómetros', $t, 'int', default: true),
            ExportField::make('power', 'Potência (CV)', $t, 'int'),
            ExportField::make('cylinder_capacity', 'Cilindrada (cc)', $t, 'int'),
            ExportField::make('color', 'Cor', $t),
            ExportField::make('register_date', 'Data 1.ª matrícula', $t, 'date'),
            ExportField::make('manufacture_date', 'Data de fabrico', $t, 'date'),

            // Valor desconhecido (estado novo) sai tal como está
            ExportField::make('status', 'Estado', $s, reader: fn (Model $v) => $v->status === null || $v->status === '' ? null : $v->status_label, default: true, width: 14),
            ExportField::make('available_to_sell_date', 'Disponível para venda desde', $s, 'date', width: 26),
            ExportField::make('asking_price', 'Preço de anúncio', $s, 'money', default: true),
            ExportField::make('show_online', 'Publicada no site', $s, 'bool'),
            ExportField::make('is_imported', 'Importada', $s, 'bool'),

            ExportField::make('client', 'Cliente (dono)', $l, reader: 'client.name', default: true, with: ['client:id,name'], width: 30),
            ExportField::make('supplier', 'Fornecedor', $l, reader: 'supplier.company_name', with: ['supplier:id,company_name'], width: 30),
            ExportField::make('seller', 'Vendedor', $l, reader: 'seller.name', with: ['seller:id,name'], width: 30),

            // Vazio se não houver legalização; "Concluída" se todos os passos estão feitos.
            // Com várias legalizações, usa-se a mais antiga (ver V3Vehicle::exportLegalization).
            ExportField::make('legalization_step', 'Passo da legalização', $o, reader: function (Model $v) {
                $legalization = $v->exportLegalization;
                if ($legalization === null) {
                    return null;
                }
                $step = $legalization->currentStep();

                return $step === null ? 'Concluída' : Legalization::PASSOS_CURTOS[$step];
            }, with: ['exportLegalization:id,v3_vehicle_id,steps_completed'], width: 20),
            ExportField::make('photos_count', 'Nº de fotos', $o, 'int', fn (Model $v) => $v->photos_count, counts: ['photos']),
            ExportField::make('documents_count', 'Nº de documentos', $o, 'int', fn (Model $v) => $v->documents_count, counts: ['documents']),
            ExportField::make('created_at', 'Criada em', $o, 'date'),
        ];
    }
}
