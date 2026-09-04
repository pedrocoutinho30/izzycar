<?php

namespace App\Services;

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

        $inner = sprintf(
            '%s -m scraper.cli sync-searches && %s -m scraper.cli run %s',
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
}
