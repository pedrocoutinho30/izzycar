<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\ClientMergeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Lista os grupos de clientes que partilham email ou telefone, com o que cada
 * registo tem associado e uma sugestão do que deve ficar. Só de leitura.
 */
class ClientDuplicates extends Command
{
    protected $signature = 'clients:duplicates';

    protected $description = 'Mostra clientes/leads que partilham email ou telefone (só leitura)';

    public function handle(ClientMergeService $merger): int
    {
        $clients = Client::get(['id', 'name', 'email', 'email_normalized', 'phone_key', 'is_lead', 'lead_source', 'owner_id', 'created_at']);

        // Agrupa por email OU telefone iguais (um cliente pode ligar grupos diferentes).
        $groups = [];
        $keys = [
            $clients->filter(fn ($c) => $c->email_normalized)->groupBy('email_normalized'),
            $clients->filter(fn ($c) => $c->phone_key)->groupBy('phone_key'),
        ];
        foreach ($keys as $byKey) {
            foreach ($byKey->filter(fn ($g) => $g->count() > 1) as $group) {
                $ids = $group->pluck('id')->all();
                $merged = false;
                foreach ($groups as &$existing) {
                    if (array_intersect($existing, $ids)) {
                        $existing = array_values(array_unique(array_merge($existing, $ids)));
                        $merged = true;
                        break;
                    }
                }
                unset($existing);
                if (!$merged) {
                    $groups[] = $ids;
                }
            }
        }

        if (!$groups) {
            $this->info('Sem duplicados.');

            return self::SUCCESS;
        }

        $tables = $merger->referencingTables();
        $this->info(count($groups) . ' grupo(s) com email ou telefone em comum.');

        foreach ($groups as $number => $ids) {
            $members = $clients->whereIn('id', $ids)->map(function (Client $client) use ($tables) {
                $related = collect($tables)->mapWithKeys(fn ($t) => [$t => DB::table($t)->where('client_id', $client->id)->count()])->filter();

                return ['client' => $client, 'related' => $related, 'total' => $related->sum()];
            })->sortByDesc(fn ($m) => [!$m['client']->is_lead, $m['total']]);

            $names = $members->map(fn ($m) => Str::lower(Str::ascii(trim($m['client']->name))))->unique();
            $keeper = $members->first()['client'];

            $this->newLine();
            $this->line('<options=bold>Grupo ' . ($number + 1) . '</> — ' . ($names->count() > 1 ? '<fg=yellow>NOMES DIFERENTES: rever antes de juntar</>' : 'mesmo nome'));
            $this->table(
                ['#', 'Nome', 'Tipo', 'Fonte', 'Criado', 'Associado'],
                $members->map(fn ($m) => [
                    $m['client']->id . ($m['client']->is($keeper) ? ' ★' : ''),
                    $m['client']->name,
                    $m['client']->is_lead ? 'lead' : 'cliente',
                    $m['client']->lead_source ?: '—',
                    $m['client']->created_at->format('Y-m-d'),
                    $m['related']->map(fn ($n, $t) => "{$t}={$n}")->implode(' ') ?: 'nada',
                ])->values()->all()
            );
            $others = $members->pluck('client.id')->reject(fn ($id) => $id === $keeper->id)->implode(',');
            $this->line("  Sugestão (★ = cliente com mais registos): <comment>php artisan clients:merge {$keeper->id} {$others}</comment>   (simula; acrescente --apply para executar)");
        }

        return self::SUCCESS;
    }
}
