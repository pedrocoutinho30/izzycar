<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PrivateFiles;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serve os ficheiros sensíveis (disco privado) a utilizadores autenticados do
 * backoffice. Ver App\Services\PrivateFiles e docs/ficheiros-privados.md.
 */
class PrivateFileController extends Controller
{
    public function show(string $tipo, string $caminho): StreamedResponse
    {
        return PrivateFiles::resposta($tipo, $caminho);
    }
}
