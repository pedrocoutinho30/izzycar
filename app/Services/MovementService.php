<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\Expense;
use App\Models\Legalization;
use App\Models\Partner;
use App\Models\Sale;
use App\Models\V3Vehicle;
use App\Models\Vehicle;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Regras dos movimentos financeiros (modelo Expense), partilhadas pelo
 * desktop (MovementV2Controller) e pela versão mobile. O ExpenseObserver
 * reflete cada gravação/eliminação em FinancialMovement.
 *
 * Movimentos com source_type são automáticos (venda, compra de viatura,
 * cotação convertida): não se editam nem eliminam aqui, só na origem.
 */
class MovementService
{
    /** Saldo de abertura usado no cálculo dos saldos da listagem. */
    public const INITIAL_BALANCE = 15757.28;

    public const ATTACHMENT_FOLDER = 'movements/attachments';

    /**
     * Regras de validação do formulário do desktop. Sem $withAttachment (mobile),
     * o comprovativo fica de fora: não se envia nem se remove por aqui.
     */
    public function rules(bool $withAttachment = true): array
    {
        $rules = [
            'movement_type'   => 'required|in:expense,income',
            'category'        => 'nullable|string|max:100',
            'v3_vehicle_id'   => 'nullable|exists:v3_vehicles,id',
            'legalization_id' => 'nullable|exists:legalizations,id',
            'client_id'       => 'nullable|exists:clients,id',
            'partner_id'      => 'nullable|exists:partners,id',
            'title'          => 'required|string|max:255',
            'amount'         => 'required|numeric|min:0',
            'vat_rate'       => 'nullable|numeric|min:0|max:100',
            'expense_date'   => 'required|date',
            'observations'   => 'nullable|string',
            'attachment'     => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:10240',
            'remove_attachment' => 'nullable|boolean',
            'payment_method' => 'nullable|string|max:50',
            'status'         => 'nullable|string|max:50',
        ];

        if (! $withAttachment) {
            unset($rules['attachment'], $rules['remove_attachment']);
        }

        return $rules;
    }

    public function isAutomatic(Expense $movement): bool
    {
        return (bool) $movement->source_type;
    }

    /** Cria um movimento manual a partir dos dados validados. */
    public function create(array $data, ?UploadedFile $attachment = null): Expense
    {
        if ($attachment) {
            $data['attachment_path'] = PrivateFiles::guardar($attachment, self::ATTACHMENT_FOLDER);
        }
        unset($data['attachment'], $data['remove_attachment']);

        $this->computeVatFields($data);

        return Expense::create($data);
    }

    /** Atualiza um movimento manual (quem chama garante que não é automático). */
    public function update(Expense $movement, array $data, ?UploadedFile $attachment = null, bool $removeAttachment = false): Expense
    {
        if ($attachment) {
            if ($movement->attachment_path) {
                PrivateFiles::apagar($movement->attachment_path);
            }
            $data['attachment_path'] = PrivateFiles::guardar($attachment, self::ATTACHMENT_FOLDER);
        } elseif ($removeAttachment) {
            if ($movement->attachment_path) {
                PrivateFiles::apagar($movement->attachment_path);
            }
            $data['attachment_path'] = null;
        }

        unset($data['attachment'], $data['remove_attachment']);

        $this->computeVatFields($data);

        $movement->update($data);

        return $movement;
    }

    /** Elimina um movimento manual e o seu comprovativo. */
    public function delete(Expense $movement): void
    {
        if ($movement->attachment_path) {
            PrivateFiles::apagar($movement->attachment_path);
        }

        $movement->delete();
    }

    /**
     * Auto-compute amount_gross, vat_amount, amount_net from amount + vat_rate.
     */
    public function computeVatFields(array &$data): void
    {
        $gross   = (float) ($data['amount'] ?? 0);

        // Taxa explicitamente por definir (movimento antigo no mobile): não se
        // inventa IVA; o valor bruto sincroniza-se e o IVA/líquido ficam como estão.
        if (array_key_exists('vat_rate', $data) && $data['vat_rate'] === null) {
            $data['amount_gross'] = $gross;
            unset($data['vat_amount'], $data['amount_net']);

            return;
        }

        $vatRate = (float) ($data['vat_rate'] ?? 0);
        $vatAmt  = $vatRate > 0 ? round($gross - ($gross / (1 + $vatRate / 100)), 2) : 0.0;

        $data['amount_gross'] = $gross;
        $data['vat_amount']   = $vatAmt;
        $data['amount_net']   = round($gross - $vatAmt, 2);
    }

    /**
     * Totais e saldos da listagem. Os totais ficam no ano escolhido, exceto
     * com datas (aí contam todos os anos, como sempre fez o desktop); os
     * saldos partem de INITIAL_BALANCE e acumulam o ano (ou tudo, com "all").
     *
     * @return array{totalExpenses: float, totalIncome: float, pending: float, saldo: float, saldoReal: float, runningBalances: array<int, float>, runningBalancesReal: array<int, ?float>}
     */
    public function summary(string $year, bool $hasDateFilter): array
    {
        $statsBase = Expense::query();
        if ($year !== 'all' && ! $hasDateFilter) {
            $statsBase->whereYear('expense_date', $year);
        }
        $totalExpenses = (float) (clone $statsBase)->where('movement_type', 'expense')->sum('amount_gross');
        $totalIncome   = (float) (clone $statsBase)->where('movement_type', 'income')->sum('amount_gross');
        $pending       = (float) (clone $statsBase)->where('status', 'pending')->sum('amount_gross');

        // Load ALL year movements sorted ascending for cumulative balance calculation
        $allYearMovements = Expense::query()
            ->when($year !== 'all', fn ($q) => $q->whereYear('expense_date', $year))
            ->orderBy('expense_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get(['id', 'movement_type', 'amount_gross', 'amount', 'status']);

        $runningBalances     = []; // inclui pendentes
        $runningBalancesReal = []; // só pagos
        $runningBalance      = self::INITIAL_BALANCE;
        $runningBalanceReal  = self::INITIAL_BALANCE;

        foreach ($allYearMovements as $m) {
            $amount = (float) ($m->amount_gross ?? $m->amount ?? 0);
            $isPaid = $m->status !== 'pending';

            if ($m->movement_type === 'income') {
                $runningBalance += $amount;
                if ($isPaid) $runningBalanceReal += $amount;
            } else {
                $runningBalance -= $amount;
                if ($isPaid) $runningBalanceReal -= $amount;
            }
            $runningBalances[$m->id]     = $runningBalance;
            $runningBalancesReal[$m->id] = $isPaid ? $runningBalanceReal : null;
        }

        return [
            'totalExpenses' => $totalExpenses,
            'totalIncome' => $totalIncome,
            'pending' => $pending,
            'saldo' => $runningBalance,
            'saldoReal' => $runningBalanceReal,
            'runningBalances' => $runningBalances,
            'runningBalancesReal' => $runningBalancesReal,
        ];
    }

    /** Anos com movimentos, do mais recente para o mais antigo (MySQL e sqlite). */
    public function availableYears(): Collection
    {
        $yearExpr = DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%Y', expense_date) AS INTEGER)"
            : 'YEAR(expense_date)';

        $years = Expense::selectRaw("{$yearExpr} as y")
            ->whereNotNull('expense_date')
            ->groupBy('y')
            ->orderByDesc('y')
            ->pluck('y');

        return $years->isEmpty() ? collect([now()->year]) : $years;
    }

    /** Listas dos selects do formulário (as mesmas do desktop). */
    public function formOptions(): array
    {
        return [
            'vehicles' => V3Vehicle::select('id', 'reference', 'brand', 'model')->orderBy('reference')->get(),
            'partners' => Partner::orderBy('name')->get(),
            'clients' => Client::select('id', 'name')->orderBy('name')->get(),
            'legalizations' => Legalization::with('client')
                ->select('id', 'client_id', 'marca', 'modelo', 'matricula')
                ->orderByDesc('id')
                ->get(),
        ];
    }

    /**
     * Origem de um movimento automático: [tipo, id] com tipo "converted",
     * "sale", "v3_vehicle" ou "vehicle"; null nos manuais ou numa origem
     * desconhecida.
     *
     * @return array{0: string, 1: int}|null
     */
    public function source(Expense $movement): ?array
    {
        if (! $movement->source_type || ! $movement->source_id) {
            return null;
        }

        $type = match (true) {
            str_starts_with($movement->source_type, ConvertedProposal::class . ':') => 'converted',
            $movement->source_type === Sale::class => 'sale',
            $movement->source_type === V3Vehicle::class => 'v3_vehicle',
            $movement->source_type === Vehicle::class => 'vehicle',
            default => null,
        };

        return $type ? [$type, (int) $movement->source_id] : null;
    }
}
