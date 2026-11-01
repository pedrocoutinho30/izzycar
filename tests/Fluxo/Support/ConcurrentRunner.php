<?php

namespace Tests\Fluxo\Support;

use RuntimeException;

/**
 * Lança N processos PHP independentes (tests/Fluxo/bin/worker.php) que fazem pedidos HTTP ao
 * kernel da aplicação contra a MESMA base MySQL de teste, sincronizados por uma barreira real:
 *
 *   1. cada worker arranca a aplicação, passa a guarda e escreve "ready.<i>";
 *   2. o runner espera pelos N "ready" e escreve atómica ("go") um instante comum (agora + 150 ms);
 *   3. cada worker espera ativamente até esse instante e dispara o pedido.
 *
 * Mede-se a dispersão real dos disparos (max-min dos inícios) como evidência de simultaneidade.
 *
 * SEGURANÇA: o ambiente do worker é construído aqui, explícito e completo, a partir das variáveis
 * forçadas de phpunit.fluxo-mysql.xml (nada herdado da shell, nada do .env).
 */
class ConcurrentRunner
{
    /** Variáveis forçadas do phpunit.fluxo-mysql.xml, tal e qual. */
    public static function ambienteForcado(): array
    {
        $xml = simplexml_load_file(self::raiz().'/phpunit.fluxo-mysql.xml');
        $env = [];
        foreach ($xml->php->server as $s) {
            $env[(string) $s['name']] = (string) $s['value'];
        }

        return $env;
    }

    public static function raiz(): string
    {
        return dirname(__DIR__, 3);
    }

    /**
     * @param  list<array{user_id?: ?int, requests: list<array{label?: string, method: string, uri: string, params?: array, json?: bool}>, config?: array}>  $jobs  um por processo
     * @return array{workers: list<array>, dispersao_ms: float, guardas_ok: int, barreira_ok: bool, falhas_arranque: list<string>, duracao_s: float}
     */
    public static function executar(array $jobs, float $timeoutPronto = 60.0): array
    {
        $inicio = microtime(true);
        $n = count($jobs);
        $dir = sys_get_temp_dir().'/fluxo-workers-'.bin2hex(random_bytes(6));
        mkdir($dir, 0700, true);
        mkdir($dir.'/storage', 0700, true);

        $env = self::ambienteForcado();
        // Garantias adicionais (mesmo valor que o XML ou só informativas).
        $env['PATH'] = '/usr/bin:/bin:/usr/local/bin:/opt/homebrew/bin';
        $env['FLUXO_WORKER'] = '1';

        // Estas variáveis têm de existir no XML, senão abortamos antes de lançar qualquer processo.
        foreach (['DB_CONNECTION' => 'mysql', 'DB_HOST' => '127.0.0.1', 'DB_DATABASE' => 'izzycar_fluxo_teste', 'DB_URL' => '', 'DB_SOCKET' => '', 'APP_ENV' => 'testing', 'MAIL_MAILER' => 'array', 'QUEUE_CONNECTION' => 'sync'] as $k => $v) {
            if (($env[$k] ?? null) !== $v) {
                throw new RuntimeException("Runner abortado: {$k} no XML não é '{$v}'.");
            }
        }

        $procs = [];
        $pipes = [];
        $worker = self::raiz().'/tests/Fluxo/bin/worker.php';
        foreach ($jobs as $i => $job) {
            $job['index'] = $i;
            $job['dir'] = $dir;
            $job['storage_dir'] = $dir.'/storage';
            $ficheiro = "{$dir}/job.{$i}.json";
            file_put_contents($ficheiro, json_encode($job));
            $p = proc_open(
                [PHP_BINARY, '-d', 'display_errors=stderr', '-d', 'memory_limit=512M', $worker, $ficheiro],
                [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes[$i],
                self::raiz(),
                $env   // ambiente EXPLÍCITO e completo
            );
            if (! is_resource($p)) {
                throw new RuntimeException('proc_open falhou');
            }
            fclose($pipes[$i][0]);
            stream_set_blocking($pipes[$i][1], false);
            stream_set_blocking($pipes[$i][2], false);
            $procs[$i] = $p;
        }

        // Esperar pelos "ready" (ou pela morte antecipada de algum worker).
        $saidas = array_fill(0, $n, '');
        $erros = array_fill(0, $n, '');
        $limite = microtime(true) + $timeoutPronto;
        $falhasArranque = [];
        while (true) {
            $prontos = count(glob($dir.'/ready.*'));
            self::drenar($pipes, $saidas, $erros);
            if ($prontos >= $n) {
                break;
            }
            $morreu = false;
            foreach ($procs as $i => $p) {
                if (! proc_get_status($p)['running'] && ! is_file($dir.'/ready.'.$i)) {
                    $morreu = true;   // morreu antes de ficar pronto (ex.: guarda): não esperar mais
                }
            }
            if ($morreu) {
                break;
            }
            if (microtime(true) > $limite) {
                break;
            }
            usleep(2000);
        }

        $go = microtime(true) + 0.15;
        file_put_contents($dir.'/go.tmp', sprintf('%.6F', $go));
        rename($dir.'/go.tmp', $dir.'/go');

        // Recolher.
        $fim = microtime(true) + $timeoutPronto;
        $abertos = $procs;
        while ($abertos && microtime(true) < $fim) {
            self::drenar($pipes, $saidas, $erros);
            foreach ($abertos as $i => $p) {
                if (! proc_get_status($p)['running']) {
                    unset($abertos[$i]);
                }
            }
            usleep(3000);
        }
        self::drenar($pipes, $saidas, $erros);
        foreach ($procs as $i => $p) {
            if (isset($abertos[$i])) {
                proc_terminate($p);
                $falhasArranque[] = "worker {$i}: timeout";
            }
            fclose($pipes[$i][1]);
            fclose($pipes[$i][2]);
            proc_close($p);
        }

        $workers = [];
        foreach ($jobs as $i => $_) {
            $linha = null;
            foreach (explode("\n", $saidas[$i]) as $l) {
                if (str_starts_with($l, '@@FLUXO-WORKER@@')) {
                    $linha = substr($l, strlen('@@FLUXO-WORKER@@'));
                }
            }
            $dados = $linha ? json_decode($linha, true) : null;
            if (! is_array($dados)) {
                $dados = ['index' => $i, 'guard_ok' => false, 'erro' => 'sem saída do worker', 'stderr' => mb_substr($erros[$i], 0, 600), 'requests' => []];
                $falhasArranque[] = "worker {$i}: sem saída (".mb_substr(trim($erros[$i]), 0, 200).')';
            } elseif (! ($dados['guard_ok'] ?? false)) {
                $falhasArranque[] = "worker {$i}: ".($dados['erro'] ?? 'guarda falhou');
            }
            $workers[$i] = $dados;
        }

        $inicios = [];
        $barreiraOk = true;
        foreach ($workers as $w) {
            foreach ($w['requests'] as $k => $r) {
                if ($k === 0) {
                    $inicios[] = $r['inicio'];
                }
            }
            if (isset($w['pronto_em']) && $w['pronto_em'] > $go) {
                $barreiraOk = false;
            }
        }
        $dispersao = $inicios ? (max($inicios) - min($inicios)) * 1000 : -1.0;

        self::limpar($dir);

        return [
            'workers' => $workers,
            'dispersao_ms' => round($dispersao, 2),
            'guardas_ok' => count(array_filter($workers, fn ($w) => $w['guard_ok'] ?? false)),
            'barreira_ok' => $barreiraOk && count($inicios) === $n,
            'falhas_arranque' => $falhasArranque,
            'duracao_s' => round(microtime(true) - $inicio, 2),
        ];
    }

    /** @param  array<int, array>  $pipes */
    private static function drenar(array $pipes, array &$saidas, array &$erros): void
    {
        foreach ($pipes as $i => $p) {
            if (is_resource($p[1])) {
                $saidas[$i] .= (string) stream_get_contents($p[1]);
            }
            if (is_resource($p[2])) {
                $erros[$i] .= (string) stream_get_contents($p[2]);
            }
        }
    }

    private static function limpar(string $dir): void
    {
        if (! str_starts_with($dir, sys_get_temp_dir().'/fluxo-workers-')) {
            return;
        }
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $f) {
            $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname());
        }
        @rmdir($dir);
    }

    // ------------------------------------------------------------------
    // Agregação
    // ------------------------------------------------------------------

    /**
     * Resume os resultados de todos os pedidos de todos os workers.
     *
     * @return array{pedidos: int, status: array<string,int>, sucessos: int, erros_flash: int, http500: int, deadlocks: int, lock_timeouts: int, unicidade: int, excecoes: list<array>, mensagens_erro: array<string,int>}
     */
    public static function agregar(array $resultado): array
    {
        $agg = ['pedidos' => 0, 'status' => [], 'sucessos' => 0, 'erros_flash' => 0, 'http500' => 0, 'deadlocks' => 0, 'lock_timeouts' => 0, 'unicidade' => 0, 'excecoes' => [], 'mensagens_erro' => []];
        foreach ($resultado['workers'] as $w) {
            foreach ($w['requests'] as $r) {
                $agg['pedidos']++;
                $st = (string) ($r['status'] ?? '?');
                $agg['status'][$st] = ($agg['status'][$st] ?? 0) + 1;
                if ((int) $st >= 500 || $st === 'exception') {
                    $agg['http500']++;
                }
                $ok = isset($r['flash']['success']) || (($r['json']['success'] ?? false) === true);
                if ($ok) {
                    $agg['sucessos']++;
                }
                $msg = $r['flash']['error'] ?? ($r['json']['message'] ?? null);
                if (isset($r['flash']['error']) || (($r['json']['success'] ?? null) === false)) {
                    $agg['erros_flash']++;
                    $agg['mensagens_erro'][(string) $msg] = ($agg['mensagens_erro'][(string) $msg] ?? 0) + 1;
                }
                foreach ($r['excecoes'] ?? [] as $e) {
                    $agg['excecoes'][] = $e;
                    $txt = ($e['mensagem'] ?? '').' '.($e['classe'] ?? '');
                    if (($e['sqlstate'] ?? null) === '40001' || ($e['codigo_driver'] ?? null) == 1213 || stripos($txt, 'deadlock') !== false) {
                        $agg['deadlocks']++;
                    } elseif (($e['codigo_driver'] ?? null) == 1205 || stripos($txt, 'lock wait timeout') !== false) {
                        $agg['lock_timeouts']++;
                    } elseif (($e['codigo_driver'] ?? null) == 1062 || stripos($txt, 'duplicate entry') !== false || stripos($e['classe'] ?? '', 'UniqueConstraint') !== false) {
                        $agg['unicidade']++;
                    }
                }
            }
        }

        return $agg;
    }
}
