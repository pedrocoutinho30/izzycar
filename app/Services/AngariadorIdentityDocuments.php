<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Documento de identificação (frente e verso) de quem se candidata a angariador.
 * Dado sensível: fica sempre em disco privado e só é servido por rotas do admin.
 */
class AngariadorIdentityDocuments
{
    public const LADOS = ['frente', 'verso'];

    private const DISCO = 'local';

    public static function guardar(User $user, UploadedFile $frente, UploadedFile $verso): void
    {
        self::apagar($user);

        $user->update([
            'documento_frente_path' => $frente->store("angariadores/documentos/{$user->id}", self::DISCO),
            'documento_verso_path' => $verso->store("angariadores/documentos/{$user->id}", self::DISCO),
        ]);
    }

    public static function tem(User $user): bool
    {
        return filled($user->documento_frente_path) || filled($user->documento_verso_path);
    }

    /** Apaga os ficheiros e limpa os caminhos (ao rejeitar ou apagar a candidatura). */
    public static function apagar(User $user): void
    {
        foreach (['documento_frente_path', 'documento_verso_path'] as $campo) {
            if ($user->{$campo}) {
                Storage::disk(self::DISCO)->delete($user->{$campo});
            }
        }

        if (self::tem($user)) {
            $user->update(['documento_frente_path' => null, 'documento_verso_path' => null]);
        }
    }

    /** Abre um dos lados no browser; 404 se não existir. */
    public static function resposta(User $user, string $lado): StreamedResponse
    {
        abort_unless(in_array($lado, self::LADOS, true), 404);

        $path = $user->{"documento_{$lado}_path"};
        abort_unless($path && Storage::disk(self::DISCO)->exists($path), 404);

        $nome = "documento_{$lado}_" . Str::slug($user->full_name) . '.' . pathinfo($path, PATHINFO_EXTENSION);

        return Storage::disk(self::DISCO)->response($path, $nome);
    }
}
