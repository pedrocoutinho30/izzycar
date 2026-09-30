<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Resumo do log "permissions": que acessos o middleware bloqueou (ou teria
 * bloqueado, no modo de ensaio), agrupados por utilizador e rota.
 */
class PermissionsReport extends Command
{
    protected $signature = 'permissions:report {--days=14 : Quantos dias de log ler}';

    protected $description = 'Mostra os acessos que as permissões bloqueiam ou bloqueariam';

    public function handle(): int
    {
        $files = collect(glob(storage_path('logs/permissions-*.log')) ?: [])
            ->sort()
            ->reverse()
            ->take((int) $this->option('days'));

        $rows = [];
        foreach ($files as $file) {
            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $entry = json_decode($line, true);
                $context = $entry['context'] ?? null;
                if (!$context) {
                    continue;
                }
                $key = ($context['user_id'] ?? '?') . '|' . ($context['route'] ?? '?') . '|' . ($context['method'] ?? '');
                $rows[$key] ??= [
                    'user' => ($context['user'] ?? '?') . ' #' . ($context['user_id'] ?? '?'),
                    'roles' => implode(', ', $context['roles'] ?? []),
                    'route' => ($context['method'] ?? '') . ' ' . ($context['route'] ?? '?'),
                    'requires' => $context['requires'] ?? '?',
                    'count' => 0,
                    'last' => '',
                ];
                $rows[$key]['count']++;
                $rows[$key]['last'] = max($rows[$key]['last'], substr($entry['datetime'] ?? '', 0, 16));
            }
        }

        if ($rows === []) {
            $this->info('Sem acessos bloqueados no período.');

            return self::SUCCESS;
        }

        $this->table(
            ['Utilizador', 'Perfis', 'Rota', 'Precisa de', 'Vezes', 'Último'],
            collect($rows)->sortByDesc('count')->map(fn ($r) => array_values($r))->all()
        );

        return self::SUCCESS;
    }
}
