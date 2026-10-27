<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Ficheiros sensíveis de clientes e do negócio (fotos de retomas, avaliações
 * de consignação, comprovativos de comissões, anexos de despesas e movimentos).
 *
 * Ficam no disco privado ('local', fora de /storage) e só são servidos pela
 * rota autenticada admin.v2.files.show. Os ficheiros anteriores a esta
 * alteração estão no disco público: enquanto o comando files:move-to-private
 * não os mover, lê-se primeiro o disco privado e, se não existir, o público
 * (o caminho relativo guardado na BD é o mesmo nos dois discos).
 *
 * Ver docs/ficheiros-privados.md.
 */
class PrivateFiles
{
    public const DISCO_PRIVADO = 'local';

    /** Disco onde estes ficheiros estavam antes (só leitura/compatibilidade). */
    public const DISCO_LEGADO = 'public';

    /** Tipo (segmento do URL) => pasta no disco. */
    public const TIPOS = [
        'retomas' => 'retoma',
        'consignacao' => 'consignment-evaluations',
        'comprovativos' => 'comissoes/comprovativos',
        'despesas' => 'expenses/attachments',
        'movimentos' => 'movements/attachments',
    ];

    /** Só estes tipos de ficheiro abrem no browser; o resto é descarregado. */
    private const INLINE_MIMES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'application/pdf'];

    public static function tipos(): array
    {
        return array_keys(self::TIPOS);
    }

    /** Guarda um upload no disco privado (devolve o caminho relativo a guardar na BD). */
    public static function guardar(UploadedFile $ficheiro, string $pasta): string
    {
        return $ficheiro->store($pasta, self::DISCO_PRIVADO);
    }

    /** Disco onde o ficheiro existe (privado primeiro, depois o legado); null se não existir. */
    public static function disco(?string $caminho): ?string
    {
        if (! self::caminhoSeguro($caminho)) {
            return null;
        }

        foreach ([self::DISCO_PRIVADO, self::DISCO_LEGADO] as $disco) {
            if (Storage::disk($disco)->exists($caminho)) {
                return $disco;
            }
        }

        return null;
    }

    public static function existe(?string $caminho): bool
    {
        return self::disco($caminho) !== null;
    }

    /** Caminho absoluto (anexos de email); null se o ficheiro não existir. */
    public static function caminhoAbsoluto(?string $caminho): ?string
    {
        $disco = self::disco($caminho);

        return $disco ? Storage::disk($disco)->path($caminho) : null;
    }

    /** Apaga o ficheiro onde quer que esteja (privado e/ou legado). */
    public static function apagar(?string $caminho): void
    {
        if (! self::caminhoSeguro($caminho)) {
            return;
        }

        foreach ([self::DISCO_PRIVADO, self::DISCO_LEGADO] as $disco) {
            Storage::disk($disco)->delete($caminho);
        }
    }

    /**
     * URL (rota autenticada) de um caminho guardado na BD, ex.
     * "comissoes/comprovativos/x.pdf". Devolve null se o caminho não pertencer
     * a nenhuma pasta privada.
     */
    public static function url(?string $caminho): ?string
    {
        if (! self::caminhoSeguro($caminho)) {
            return null;
        }

        foreach (self::TIPOS as $tipo => $pasta) {
            if (str_starts_with($caminho, $pasta . '/')) {
                return route('admin.v2.files.show', [
                    'tipo' => $tipo,
                    'caminho' => substr($caminho, strlen($pasta) + 1),
                ]);
            }
        }

        return null;
    }

    /** Extensão em minúsculas (para decidir entre imagem e ligação nas views). */
    public static function extensao(?string $caminho): string
    {
        return strtolower(pathinfo((string) $caminho, PATHINFO_EXTENSION));
    }

    /**
     * Resposta com o ficheiro de um tipo; 404 se o tipo ou o caminho forem
     * inválidos, saírem da pasta do tipo ou o ficheiro não existir.
     */
    public static function resposta(string $tipo, string $caminho): StreamedResponse
    {
        abort_unless(isset(self::TIPOS[$tipo]), 404);

        $completo = self::TIPOS[$tipo] . '/' . $caminho;
        abort_unless(self::caminhoSeguro($caminho), 404);

        $disco = self::disco($completo);
        abort_unless($disco !== null, 404);

        // Defesa extra: o ficheiro resolvido tem de ficar dentro da pasta do tipo.
        $raiz = realpath(Storage::disk($disco)->path(self::TIPOS[$tipo]));
        $real = realpath(Storage::disk($disco)->path($completo));
        abort_unless($raiz && $real && str_starts_with($real, $raiz . DIRECTORY_SEPARATOR), 404);

        $armazenamento = Storage::disk($disco);
        $inline = in_array($armazenamento->mimeType($completo), self::INLINE_MIMES, true);

        $resposta = $armazenamento->response(
            $completo,
            basename($completo),
            [],
            $inline ? 'inline' : 'attachment'
        );
        $resposta->headers->set('X-Content-Type-Options', 'nosniff');
        $resposta->headers->set('Cache-Control', 'private, max-age=0, must-revalidate');

        return $resposta;
    }

    /** Rejeita caminhos vazios, absolutos, com ".." ou com caracteres de controlo. */
    public static function caminhoSeguro(?string $caminho): bool
    {
        if ($caminho === null || $caminho === '' || preg_match('/[\x00-\x1F\\\\]/', $caminho)) {
            return false;
        }

        if (str_starts_with($caminho, '/')) {
            return false;
        }

        foreach (explode('/', $caminho) as $segmento) {
            if ($segmento === '' || $segmento === '.' || $segmento === '..') {
                return false;
            }
        }

        return true;
    }
}
