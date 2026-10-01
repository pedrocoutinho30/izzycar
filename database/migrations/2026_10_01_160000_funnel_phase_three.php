<?php

use App\Models\ConvertedProposal;
use App\Models\Expense;
use App\Services\ClientMatcher;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 3 do funil — dados e números:
 * - clients.email_normalized / phone_key: chaves indexadas da regra única de
 *   duplicados (ClientMatcher), preenchidas para os clientes existentes. NÃO
 *   se juntam duplicados aqui: isso faz-se à mão (php artisan clients:duplicates);
 * - converted_proposals.*_tranche_paga_em: data em que cada tranche foi recebida;
 * - lançamentos financeiros das tranches já recebidas, da comissão e dos custos
 *   (exceto o valor do carro) já pagos;
 * - expenses.expense_category aceita nulo (em produção já aceita; nas
 *   migrations não — ambientes novos e testes ficavam diferentes).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('email_normalized')->nullable()->after('email')->index();
            $table->string('phone_key', 9)->nullable()->after('phone')->index();
        });

        $matcher = new ClientMatcher();
        DB::table('clients')->orderBy('id')->get(['id', 'email', 'phone'])->each(function ($client) use ($matcher) {
            DB::table('clients')->where('id', $client->id)->update([
                'email_normalized' => $matcher->normalizeEmail($client->email),
                'phone_key' => $matcher->phoneKey($client->phone),
            ]);
        });

        Schema::table('converted_proposals', function (Blueprint $table) {
            $table->date('primeira_tranche_paga_em')->nullable()->after('primeira_tranche_pago');
            $table->date('segunda_tranche_paga_em')->nullable()->after('segunda_tranche_pago');
        });

        $expenseCategory = collect(Schema::getColumns('expenses'))->firstWhere('name', 'expense_category');
        if ($expenseCategory && !$expenseCategory['nullable']) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->text('expense_category')->nullable()->change();
            });
        }

        // Tranches já recebidas: sem data guardada, usa-se a da última alteração.
        DB::table('converted_proposals')->where('primeira_tranche_pago', true)->whereNull('primeira_tranche_paga_em')
            ->update(['primeira_tranche_paga_em' => DB::raw('DATE(updated_at)')]);
        DB::table('converted_proposals')->where('segunda_tranche_pago', true)->whereNull('segunda_tranche_paga_em')
            ->update(['segunda_tranche_paga_em' => DB::raw('DATE(updated_at)')]);

        // Tranches, comissão e custos que já estavam pagos: lançados com a data da
        // última alteração da cotação convertida (não há outra — é aproximada).
        $paidColumns = ['primeira_tranche_pago', 'segunda_tranche_pago', 'comissao_paga', 'inspecao_origem_pago', 'transporte_pago',
            'ipo_pago', 'isv_pago', 'imt_pago', 'matricula_pago_impressa', 'registo_pago'];

        ConvertedProposal::query()
            ->where(function ($q) use ($paidColumns) {
                foreach ($paidColumns as $column) {
                    $q->orWhere($column, true);
                }
            })
            ->each(fn (ConvertedProposal $converted) => Expense::syncFromConvertedProposal($converted, $converted->updated_at?->toDateString()));
    }

    public function down(): void
    {
        Schema::table('converted_proposals', function (Blueprint $table) {
            $table->dropColumn(['primeira_tranche_paga_em', 'segunda_tranche_paga_em']);
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['email_normalized', 'phone_key']);
        });
    }
};
