<?php

namespace App\Console\Commands;

use App\Services\PrivateFiles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Move do disco público para o privado os ficheiros sensíveis que já existiam
 * (pastas de App\Services\PrivateFiles::TIPOS). Só mexe em ficheiros: não usa a
 * base de dados, porque o caminho relativo é igual nos dois discos.
 */
class MoveFilesToPrivate extends Command
{
    protected $signature = 'files:move-to-private {--apply : Move mesmo os ficheiros (sem esta opção só lista)}';

    protected $description = 'Move do disco público para o privado os ficheiros sensíveis de clientes (dry-run por omissão)';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $publico = Storage::disk(PrivateFiles::DISCO_LEGADO);
        $privado = Storage::disk(PrivateFiles::DISCO_PRIVADO);

        $this->info($apply ? 'A mover ficheiros para o disco privado...' : 'Dry-run: nada será movido (usar --apply para mover).');

        $encontrados = $movidos = $jaPrivados = $falhas = 0;

        foreach (PrivateFiles::TIPOS as $pasta) {
            foreach ($publico->allFiles($pasta) as $caminho) {
                $encontrados++;

                // Já existe no privado com o mesmo tamanho: só falta limpar o público.
                $copiado = $privado->exists($caminho) && $privado->size($caminho) === $publico->size($caminho);
                if ($copiado) {
                    $jaPrivados++;
                } elseif ($apply && ! $this->copiar($caminho)) {
                    $this->error("  FALHOU (copiar): {$caminho}");
                    $falhas++;

                    continue;
                }

                if (! $apply) {
                    $this->line(($copiado ? '  [já copiado, limpar público] ' : '  [mover] ') . $caminho);

                    continue;
                }

                // Só apaga o público depois de confirmar que a cópia existe e tem o mesmo tamanho.
                if ($privado->exists($caminho) && $privado->size($caminho) === $publico->size($caminho)) {
                    $publico->delete($caminho);
                    $movidos++;
                    $this->line("  movido: {$caminho}");
                } else {
                    $this->error("  FALHOU (verificar): {$caminho}");
                    $falhas++;
                }
            }
        }

        $this->newLine();
        $this->info("{$encontrados} ficheiro(s) no disco público nas pastas privadas ({$jaPrivados} já tinham cópia privada).");
        if ($apply) {
            $this->info("{$movidos} movido(s), {$falhas} falha(s).");
        } else {
            $this->comment('Dry-run: nada foi alterado.');
        }

        return $falhas > 0 ? self::FAILURE : self::SUCCESS;
    }

    /** Copia em stream (sem carregar o ficheiro em memória). */
    private function copiar(string $caminho): bool
    {
        $stream = Storage::disk(PrivateFiles::DISCO_LEGADO)->readStream($caminho);
        if (! is_resource($stream)) {
            return false;
        }

        try {
            return (bool) Storage::disk(PrivateFiles::DISCO_PRIVADO)->writeStream($caminho, $stream);
        } finally {
            fclose($stream);
        }
    }
}
