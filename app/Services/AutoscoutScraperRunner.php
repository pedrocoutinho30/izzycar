<?php

namespace App\Services;

use Symfony\Component\Process\Process;

/**
 * Dispara o scraper Python (scarperAutoscout/) em segundo plano a partir do
 * Laravel, para uma pesquisa recém-criada ou para atualizar uma já existente.
 *
 * Corre em background (não bloqueia o pedido HTTP) - o progresso fica visível
 * através de radar_search_runs, que o próprio script Python vai atualizando
 * (ver scraper/db.py::start_run/finish_run), sem o Laravel precisar de saber
 * nada sobre o estado do processo em si.
 *
 * Usa `exec()` + `&` em vez do facade Process do Laravel de propósito: o
 * objeto devolvido por Process::start() é destruído (garbage collected) mal o
 * método regressa, e o destrutor do Symfony Process mata o processo filho
 * nesse momento - o scraper morria a meio sem chegar a escrever nada.
 *
 * O redireccionamento duplo (o subshell em segundo plano tem o seu próprio
 * ">> log 2>&1", e o comando exterior *também* redireciona para
 * "/dev/null 2>&1") é necessário para desanexar por completo: sem o segundo
 * redireccionamento, o exec() do PHP fica preso ~2s à espera (o próprio
 * ficheiro descritor herdado do pipe do PHP só fecha quando o processo
 * scraper inteiro termina, apesar de "&" o pôr em segundo plano).
 */
class AutoscoutScraperRunner
{
    private const SCRAPER_DIR = 'scarperAutoscout';

    public function syncAndRun(string $searchName): void
    {
        $dir = base_path(self::SCRAPER_DIR);
        $python = $dir.'/venv/bin/python';
        $logFile = storage_path('logs/radar-scraper.log');

        // -u (unbuffered): sem isto, o Python só escreve no ficheiro de log quando o
        // buffer interno enche ou o processo termina - útil para "tail -f" ao log
        // durante uma recolha longa em vez de só ver tudo de repente no fim.
        $inner = sprintf(
            '%s -u -m scraper.cli sync-searches && %s -u -m scraper.cli run %s',
            escapeshellarg($python),
            escapeshellarg($python),
            escapeshellarg($searchName)
        );

        $command = sprintf(
            'cd %s && (%s >> %s 2>&1 &) > /dev/null 2>&1',
            escapeshellarg($dir),
            $inner,
            escapeshellarg($logFile)
        );

        exec($command);
    }

    /**
     * Sincroniza tudo a partir do YAML e corre só as pesquisas ativas
     * (radar_searches.is_active), UMA a seguir à outra. Ao contrário de
     * syncAndRun(), corre em BLOCO (não em segundo plano) - é chamado a partir de
     * um comando artisan invocado diretamente por um cron, não de um pedido HTTP,
     * por isso não há problema em esperar (pode demorar minutos com várias
     * pesquisas × 8 países da AutoScout24 cada).
     */
    public function runActiveSearches(?callable $onOutput = null): bool
    {
        $dir = base_path(self::SCRAPER_DIR);
        $python = $dir.'/venv/bin/python';

        // -u (unbuffered): sem isto, o Python só entrega o output ao Process do
        // Laravel quando o buffer interno enche ou o processo termina - a saída ao
        // vivo do $onOutput ficava "presa" durante minutos numa recolha longa.
        $command = sprintf(
            '%s -u -m scraper.cli run-active',
            escapeshellarg($python)
        );

        $process = Process::fromShellCommandline($command, $dir);
        $process->setTimeout(3300); // um pouco menos de 1h, para nunca se sobrepor à próxima chamada do cron
        $process->run($onOutput);

        return $process->isSuccessful();
    }
}
