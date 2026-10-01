<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\ClientMergeService;
use Illuminate\Console\Command;

class ClientMerge extends Command
{
    protected $signature = 'clients:merge {keep : id do registo que fica} {remove* : ids dos duplicados a juntar} {--apply : executar (sem isto só simula)}';

    protected $description = 'Junta registos duplicados num só. Simula por omissão; --apply executa.';

    public function handle(ClientMergeService $merger): int
    {
        $keep = Client::find($this->argument('keep'));
        $remove = Client::whereIn('id', $this->argument('remove'))->get();

        if (!$keep || $remove->count() !== count(array_unique($this->argument('remove')))) {
            $this->error('Algum dos ids não existe.');

            return self::FAILURE;
        }

        $apply = (bool) $this->option('apply');
        $this->line("Fica: #{$keep->id} {$keep->name} (" . ($keep->is_lead ? 'lead' : 'cliente') . ')');
        foreach ($remove as $client) {
            $this->line("Junta: #{$client->id} {$client->name} (" . ($client->is_lead ? 'lead' : 'cliente') . ')');
        }

        $result = $merger->merge($keep, $remove, apply: false);
        $this->table(['Tabela', 'Registos a passar para #' . $keep->id], collect($result['moved'])->map(fn ($n, $t) => [$t, $n])->values()->all());
        $this->line('Campos que o registo que fica herda: ' . ($result['filled'] ? implode(', ', $result['filled']) : 'nenhum'));
        $this->line('Fica como: ' . ($result['is_lead'] ? 'lead' : 'cliente'));

        if (!$apply) {
            $this->warn('Simulação — nada foi alterado. Acrescente --apply para executar.');

            return self::SUCCESS;
        }

        if (!$this->confirm('Isto elimina os registos duplicados e não se desfaz. Continuar?', false)) {
            return self::SUCCESS;
        }

        $merger->merge($keep, $remove, apply: true);
        $this->info('Registos juntados.');

        return self::SUCCESS;
    }
}
