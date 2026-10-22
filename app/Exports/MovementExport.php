<?php

namespace App\Exports;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Exportação de movimentos (Expense). O valor sai sempre positivo, com o tipo
 * (Despesa/Receita) numa coluna à parte; não há colunas de entrada/saída, saldo
 * nem totais. Não inclui o caminho do anexo (só "Tem anexo"), a origem em bruto,
 * a viatura V1, a categoria legada nem o campo "amount" (redundante).
 */
class MovementExport extends ExportDefinition
{
    /** Mesmas colunas para os três campos da viatura: o with() tem de ser idêntico para não se sobreporem. */
    private const VEHICLE = 'v3Vehicle:id,reference,brand,model,registration';

    public function type(): string
    {
        return 'movements';
    }

    public function filenamePrefix(): string
    {
        return 'movimentos';
    }

    public function title(): string
    {
        return 'Movimentos';
    }

    public function filterKeys(): array
    {
        return ['search', 'movement_type', 'category', 'status', 'v3_vehicle_id', 'client_id', 'year', 'date_from', 'date_to'];
    }

    /**
     * Sem datas nem ano, aplica-se o ano corrente (como a listagem). Fica explícito
     * nos filtros para constar assim na auditoria; "year" ignora-se se houver datas
     * (o scope também o faz) e deixa de constar.
     */
    public function normalizeFilters(array $filters): array
    {
        if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
            unset($filters['year']);
        } elseif (!isset($filters['year']) || $filters['year'] === '') {
            $filters['year'] = (string) now()->year;
        }

        return $filters;
    }

    protected function baseQuery(array $filters): Builder
    {
        return Expense::query()->filtrar($filters);
    }

    /**
     * Ordem da listagem (data e criação mais recentes primeiro; o id desempata
     * para a paginação por offset ser determinista). lazyById/chunkById ordenam
     * só por id, por isso usa-se lazy() (lotes por offset, com eager loading e
     * memória constante). Risco aceite: alterar movimentos a meio de uma
     * exportação grande pode repetir ou saltar uma linha.
     */
    public function rows(Builder $query, int $chunk): iterable
    {
        return $query
            ->orderByDesc('expense_date')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->lazy($chunk);
    }

    protected function defineFields(): array
    {
        $m = 'Movimento';
        $a = 'Associações';
        $p = 'Pagamento e IVA';
        $o = 'Outros';

        return [
            ExportField::make('expense_date', 'Data', $m, 'date', default: true),
            ExportField::make('movement_type', 'Tipo', $m, reader: fn (Model $e) => Expense::movementTypes()[$e->movement_type] ?? $e->movement_type, default: true),
            ExportField::make('category', 'Categoria', $m, reader: fn (Model $e) => $e->category_label, default: true, width: 24),
            ExportField::make('title', 'Descrição', $m, default: true, width: 45),
            ExportField::make('amount', 'Valor (c/ IVA)', $m, 'money', fn (Model $e) => $e->amount_gross ?? $e->amount, default: true),
            ExportField::make('status', 'Estado', $m, reader: fn (Model $e) => Expense::statuses()[$e->status] ?? $e->status, default: true),

            ExportField::make('vehicle_reference', 'Viatura (referência)', $a, reader: 'v3Vehicle.reference', default: true, with: [self::VEHICLE]),
            ExportField::make('vehicle_name', 'Viatura (marca e modelo)', $a, reader: function (Model $e) {
                $name = trim(($e->v3Vehicle?->brand ?? '') . ' ' . ($e->v3Vehicle?->model ?? ''));

                return $name === '' ? null : $name;
            }, with: [self::VEHICLE], width: 28),
            ExportField::make('vehicle_registration', 'Matrícula', $a, reader: 'v3Vehicle.registration', with: [self::VEHICLE]),
            ExportField::make('client', 'Cliente', $a, reader: 'client.name', with: ['client:id,name'], width: 30),
            ExportField::make('partner', 'Parceiro', $a, reader: 'partner.name', with: ['partner:id,name'], width: 30),
            ExportField::make('legalization_id', 'Legalização (nº)', $a, 'int'),

            ExportField::make('payment_method', 'Método de pagamento', $p, reader: fn (Model $e) => $e->payment_method === null ? null : (Expense::paymentMethods()[$e->payment_method] ?? $e->payment_method), width: 24),
            ExportField::make('vat_rate', 'Taxa IVA', $p, 'percent'),
            ExportField::make('vat_amount', 'Valor IVA', $p, 'money'),
            ExportField::make('amount_net', 'Valor líquido', $p, 'money'),

            ExportField::make('origin', 'Origem', $o, reader: fn (Model $e) => empty($e->source_type) ? 'Manual' : 'Automático'),
            ExportField::make('has_attachment', 'Tem anexo', $o, 'bool', fn (Model $e) => !empty($e->attachment_path)),
            ExportField::make('observations', 'Observações', $o, width: 50),
            ExportField::make('created_at', 'Criado em', $o, 'datetime'),
        ];
    }
}
