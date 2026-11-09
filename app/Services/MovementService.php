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
    /**
     * Só contam e aparecem movimentos a partir desta data; o que é anterior
     * fica na base de dados mas fora da listagem, dos totais e dos saldos.
     */
    public const DATA_INICIO = '2026-01-01';

    /**
     * Saldo de abertura (em DATA_INICIO): os saldos partem daqui e somam só os
     * movimentos desde essa data.
     */
    public const INITIAL_BALANCE = 15757.28;

    public const ATTACHMENT_FOLDER = 'movements/attachments';

    /**
     * Regras de validação do formulário do desktop. Sem $withAttachment (mobile),
     * o comprovativo fica de fora: não se envia nem se remove por aqui.
     */
    public function rules(bool $withAttachment = true, ?Expense $current = null): array
    {
        $rules = [
            'movement_type'   => 'required|in:expense,income',
            'category'        => ['nullable', 'string', 'max:100', $this->categoryRule(fn () => request()->input('movement_type'), $current?->category)],
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

    /**
     * A categoria tem de pertencer ao tipo do movimento (despesa/receita). Num movimento
     * antigo mantém-se a que já tinha, mesmo que já não se escolha em movimentos novos.
     *
     * @param  \Closure(): mixed  $type  tipo do movimento no momento da validação
     */
    public function categoryRule(\Closure $type, ?string $atual = null): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($type, $atual) {
            $tipo = $type();
            $tipo = is_string($tipo) ? $tipo : null;

            if ($tipo === null || ! isset(Expense::movementTypes()[$tipo])) {
                return; // o tipo inválido já tem o seu próprio erro
            }
            if ($value === $atual || Expense::categoryAllowed(is_string($value) ? $value : null, $tipo)) {
                return;
            }

            $fail('Esta categoria não se aplica a ' . ($tipo === 'income' ? 'receitas' : 'despesas') . '.');
        };
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

    /**
     * Atualiza um movimento manual (quem chama garante que não é automático).
     * O comprovativo novo guarda-se primeiro, a BD atualiza numa transação e o
     * antigo só se apaga depois do commit; se a gravação falhar, o novo é
     * removido e o antigo fica intacto (LOG-017).
     */
    public function update(Expense $movement, array $data, ?UploadedFile $attachment = null, bool $removeAttachment = false): Expense
    {
        $previousPath = $movement->attachment_path;
        $newPath = null;

        if ($attachment) {
            $newPath = PrivateFiles::guardar($attachment, self::ATTACHMENT_FOLDER);
            $data['attachment_path'] = $newPath;
        } elseif ($removeAttachment) {
            $data['attachment_path'] = null;
        }

        unset($data['attachment'], $data['remove_attachment']);

        $this->computeVatFields($data);

        try {
            DB::transaction(fn () => $movement->update($data));
        } catch (\Throwable $e) {
            if ($newPath) {
                PrivateFiles::apagar($newPath);
            }

            throw $e;
        }

        if ($previousPath && array_key_exists('attachment_path', $data) && $data['attachment_path'] !== $previousPath) {
            PrivateFiles::apagar($previousPath);
        }

        return $movement;
    }

    /** Elimina um movimento manual e o seu comprovativo. */
    public function delete(Expense $movement): void
    {
        $path = $movement->attachment_path;

        DB::transaction(fn () => $movement->delete());

        // O comprovativo só se apaga depois de o movimento sair da BD.
        if ($path) {
            PrivateFiles::apagar($path);
        }
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
     * com datas (aí contam todos os anos, como sempre fez o desktop). Os
     * saldos partem de INITIAL_BALANCE mais tudo o que aconteceu antes do ano
     * escolhido (saldo de abertura do ano) e acumulam o ano (ou tudo, com "all").
     *
     * "pending" é o valor líquido dos pendentes (entradas menos saídas);
     * pendingIncome e pendingExpenses trazem cada lado separado. As somas fazem-se
     * em cêntimos inteiros para não acumular erro de float.
     *
     * @return array{totalExpenses: float, totalIncome: float, pending: float, pendingIncome: float, pendingExpenses: float, openingBalance: float, openingBalanceReal: float, saldo: float, saldoReal: float, runningBalances: array<int, float>, runningBalancesReal: array<int, ?float>}
     */
    public function summary(string $year, bool $hasDateFilter): array
    {
        $statsBase = Expense::query()->contabilizaveis();
        if ($year !== 'all' && ! $hasDateFilter) {
            $statsBase->whereYear('expense_date', $year);
        }
        $totalExpenses  = $this->sumCents(clone $statsBase, 'expense');
        $totalIncome    = $this->sumCents(clone $statsBase, 'income');
        $pendingIncome  = $this->sumCents((clone $statsBase)->where('status', 'pending'), 'income');
        $pendingExpense = $this->sumCents((clone $statsBase)->where('status', 'pending'), 'expense');

        // Saldo de abertura do ano: o inicial mais todos os movimentos de anos anteriores.
        $opening     = $this->toCents(self::INITIAL_BALANCE);
        $openingReal = $opening;
        if ($year !== 'all' && ctype_digit($year)) {
            $before = fn () => Expense::query()->contabilizaveis()->where('expense_date', '<', $year . '-01-01');
            $opening     += $this->sumCents($before(), 'income') - $this->sumCents($before(), 'expense');
            $openingReal += $this->sumCents($before()->where(fn ($q) => $q->where('status', '!=', 'pending')->orWhereNull('status')), 'income')
                - $this->sumCents($before()->where(fn ($q) => $q->where('status', '!=', 'pending')->orWhereNull('status')), 'expense');
        }

        // Load ALL year movements sorted ascending for cumulative balance calculation
        $allYearMovements = Expense::query()
            ->contabilizaveis()
            ->when($year !== 'all', fn ($q) => $q->whereYear('expense_date', $year))
            ->orderBy('expense_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get(['id', 'movement_type', 'amount_gross', 'amount', 'status']);

        $runningBalances     = []; // inclui pendentes
        $runningBalancesReal = []; // só pagos
        $runningBalance      = $opening;
        $runningBalanceReal  = $openingReal;

        foreach ($allYearMovements as $m) {
            $amount = $this->toCents($m->amount_gross ?? $m->amount ?? 0);
            $isPaid = $m->status !== 'pending';

            if ($m->movement_type === 'income') {
                $runningBalance += $amount;
                if ($isPaid) $runningBalanceReal += $amount;
            } else {
                $runningBalance -= $amount;
                if ($isPaid) $runningBalanceReal -= $amount;
            }
            $runningBalances[$m->id]     = $this->fromCents($runningBalance);
            $runningBalancesReal[$m->id] = $isPaid ? $this->fromCents($runningBalanceReal) : null;
        }

        return [
            'totalExpenses' => $this->fromCents($totalExpenses),
            'totalIncome' => $this->fromCents($totalIncome),
            'pending' => $this->fromCents($pendingIncome - $pendingExpense),
            'pendingIncome' => $this->fromCents($pendingIncome),
            'pendingExpenses' => $this->fromCents($pendingExpense),
            'openingBalance' => $this->fromCents($opening),
            'openingBalanceReal' => $this->fromCents($openingReal),
            'saldo' => $this->fromCents($runningBalance),
            'saldoReal' => $this->fromCents($runningBalanceReal),
            'runningBalances' => $runningBalances,
            'runningBalancesReal' => $runningBalancesReal,
        ];
    }

    private function fromCents(int $cents): float
    {
        return $cents / 100;
    }

    private function toCents(mixed $valor): int
    {
        return (int) round(((float) $valor) * 100);
    }

    /** Soma de amount_gross (ou amount) de um tipo de movimento, em cêntimos. */
    private function sumCents(\Illuminate\Database\Eloquent\Builder $query, string $type): int
    {
        return $query->where('movement_type', $type)
            ->get(['amount_gross', 'amount'])
            ->sum(fn ($m) => $this->toCents($m->amount_gross ?? $m->amount ?? 0));
    }

    /** Anos com movimentos, do mais recente para o mais antigo (MySQL e sqlite). */
    public function availableYears(): Collection
    {
        $yearExpr = DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%Y', expense_date) AS INTEGER)"
            : 'YEAR(expense_date)';

        $years = Expense::selectRaw("{$yearExpr} as y")
            ->whereNotNull('expense_date')
            ->where('expense_date', '>=', self::DATA_INICIO)
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
