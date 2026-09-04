<?php

namespace App\Console\Commands;

use App\Services\AutoscoutScraperRunner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Atualiza automaticamente todas as pesquisas do Radar de Preços marcadas como
 * ativas - pensado para ser chamado periodicamente por um cron (ver instruções
 * de crontab dadas ao utilizador; corre 3x por dia, não a cada hora, porque uma
 * pesquisa com a AutoScout24 configurada para os 8 países da Europa já demora
 * minutos, e várias pesquisas seguidas podem levar bastante tempo).
 */
class RefreshActiveRadarSearches extends Command
{
    protected $signature = 'radar:refresh-active';
    protected $description = 'Corre novamente (em bloco) todas as pesquisas do radar marcadas como ativas';

    public function handle(AutoscoutScraperRunner $runner): int
    {
        // Evita duas execuções sobrepostas se a anterior ainda não tiver acabado
        // (pode acontecer se houver muitas pesquisas ativas) - sem isto, corriam
        // dois scrapes em simultâneo, o dobro de pedidos aos sites de origem.
        $lock = Cache::lock('radar:refresh-active', 3300);
        if (!$lock->get()) {
            $this->warn('Já há uma atualização das pesquisas ativas em curso - a saltar esta execução.');

            return Command::SUCCESS;
        }

        try {
            $ok = $runner->runActiveSearches(function ($type, $buffer) {
                $this->output->write($buffer);
            });

            return $ok ? Command::SUCCESS : Command::FAILURE;
        } finally {
            $lock->release();
        }
    }
}
