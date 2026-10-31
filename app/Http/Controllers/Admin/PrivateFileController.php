<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PrivateFiles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serve os ficheiros sensíveis (disco privado) a utilizadores autenticados do
 * backoffice. Ver App\Services\PrivateFiles e docs/ficheiros-privados.md.
 *
 * Cada tipo exige acesso ao objeto dono (PrivateFiles::HABILIDADES). Como o
 * resto do catálogo de permissões, com PERMISSIONS_ENFORCE desligado só regista
 * no log (canal "permissions"); ligado, responde 403.
 */
class PrivateFileController extends Controller
{
    public function show(Request $request, string $tipo, string $caminho): StreamedResponse
    {
        $habilidade = PrivateFiles::HABILIDADES[$tipo] ?? null;
        $user = $request->user();

        if ($habilidade !== null && ! ($user && $user->can($habilidade))) {
            $enforce = (bool) config('permissions.enforce');

            Log::channel('permissions')->warning($enforce ? 'blocked' : 'would_block', [
                'user_id' => $user?->getKey(),
                'route' => 'admin.v2.files.show',
                'tipo' => $tipo,
                'requires' => $habilidade,
            ]);

            abort_if($enforce, 403, 'Não tem permissão para abrir este ficheiro.');
        }

        return PrivateFiles::resposta($tipo, $caminho);
    }
}
