<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Client;
use App\Models\LeadActivity;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

/**
 * Junta registos duplicados do mesmo cliente num só: tudo o que aponta para
 * os duplicados (cotações, pedidos, simulações, atividades, legalizações,
 * viaturas, despesas…) passa para o registo que fica. Só se usa à mão e
 * depois de rever cada grupo — o mesmo email/telefone pode ser de pessoas
 * diferentes.
 */
class ClientMergeService
{
    /** Campos que o registo que fica herda se estiverem vazios. */
    private const FILL_FIELDS = [
        'email', 'phone', 'vat_number', 'birth_date', 'gender', 'language',
        'identification_number', 'validate_identification_number',
        'address', 'postal_code', 'city', 'client_type', 'origin', 'lead_source',
        'owner_id', 'angariador_code',
    ];

    /** @return list<string> tabelas (exceto clients) com uma coluna client_id */
    public function referencingTables(): array
    {
        return collect(Schema::getTables())
            ->pluck('name')
            ->reject(fn ($table) => $table === 'clients')
            ->filter(fn ($table) => Schema::hasColumn($table, 'client_id'))
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Client>  $remove
     * @return array{moved: array<string, int>, filled: list<string>, is_lead: bool}
     */
    public function merge(Client $keep, Collection $remove, bool $apply): array
    {
        if ($remove->isEmpty() || $remove->contains(fn (Client $c) => $c->is($keep))) {
            throw new InvalidArgumentException('Indique pelo menos um registo a juntar, diferente do que fica.');
        }

        $ids = $remove->pluck('id')->all();

        if (!$apply) {
            return $this->plan($keep, $remove, $ids)['result'];
        }

        return DB::transaction(function () use ($keep, $remove, $ids) {
            // Bloqueia os registos envolvidos e recalcula tudo já dentro da transação,
            // para as contagens (auditoria) corresponderem ao que realmente se move.
            Client::whereKey([$keep->getKey(), ...$ids])->lockForUpdate()->get();
            // Recarrega as mesmas instâncias (quem chama continua a ver o $keep atualizado).
            $keep->refresh();
            $remove->each(fn (Client $c) => $c->refresh());

            ['result' => $result, 'moved' => $moved, 'fill' => $fill, 'allLeads' => $allLeads] = $this->plan($keep, $remove, $ids);

            foreach (array_keys($moved) as $table) {
                DB::table($table)->whereIn('client_id', $ids)->update(['client_id' => $keep->id]);
            }

            $observations = collect([$keep->observation])->merge($remove->pluck('observation'))->filter()->unique()->values();
            $earliest = $remove->pluck('created_at')->push($keep->created_at)->filter()->min();
            $converted = $remove->pluck('converted_at')->push($keep->converted_at)->filter()->min();

            foreach ($remove as $duplicate) {
                AuditLog::where('auditable_type', Client::class)->where('auditable_id', $duplicate->id)->update(['auditable_id' => $keep->id]);

                LeadActivity::log(
                    $keep->id,
                    'Registo duplicado unificado',
                    "O registo #{$duplicate->id} («{$duplicate->name}») foi juntado a este.",
                    'bi-diagram-2-fill',
                    'secondary'
                );

                AuditLog::record(
                    'client_merged',
                    "Cliente #{$duplicate->id} «{$duplicate->name}» juntado ao #{$keep->id} «{$keep->name}»",
                    $keep,
                    $duplicate->only(['id', 'name', 'email', 'phone', 'is_lead', 'lead_source', 'owner_id', 'created_at']),
                    ['moved' => $moved]
                );

                $duplicate->deleteQuietly();
            }

            // Só depois de apagar os duplicados: o email/telefone herdado é único em clients.
            $keep->forceFill($fill + [
                'observation' => $observations->isEmpty() ? null : $observations->implode("\n\n"),
                'data_processing_consent' => $keep->data_processing_consent || $remove->contains(fn (Client $c) => $c->data_processing_consent),
                'newsletter_consent' => $keep->newsletter_consent || $remove->contains(fn (Client $c) => $c->newsletter_consent),
                'is_lead' => $allLeads,
                'converted_at' => $allLeads ? null : $converted,
                'created_at' => $earliest,
            ])->save();

            return $result;
        });
    }

    /**
     * Contagens e campos a herdar.
     *
     * @param  Collection<int, Client>  $remove
     * @param  list<int>  $ids
     * @return array{result: array{moved: array<string, int>, filled: list<string>, is_lead: bool}, moved: array<string, int>, fill: array<string, mixed>, allLeads: bool}
     */
    private function plan(Client $keep, Collection $remove, array $ids): array
    {
        $moved = [];
        foreach ($this->referencingTables() as $table) {
            $count = DB::table($table)->whereIn('client_id', $ids)->count();
            if ($count) {
                $moved[$table] = $count;
            }
        }

        $fill = [];
        foreach (self::FILL_FIELDS as $field) {
            if (blank($keep->{$field})) {
                $value = $remove->map(fn (Client $c) => $c->{$field})->first(fn ($v) => filled($v));
                if (filled($value)) {
                    $fill[$field] = $value;
                }
            }
        }

        $allLeads = $keep->is_lead && $remove->every(fn (Client $c) => $c->is_lead);

        return [
            'result' => ['moved' => $moved, 'filled' => array_keys($fill), 'is_lead' => $allLeads],
            'moved' => $moved,
            'fill' => $fill,
            'allLeads' => $allLeads,
        ];
    }
}
