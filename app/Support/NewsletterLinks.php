<?php

namespace App\Support;

use Illuminate\Support\Facades\URL;

/**
 * Links públicos da newsletter. São sempre assinados (SEG-013): quem não tem o
 * email recebido não consegue retirar nem dar consentimento em nome de terceiros.
 * O link de cancelamento não expira (tem de funcionar em emails antigos).
 */
final class NewsletterLinks
{
    public static function unsubscribe(?string $email): string
    {
        return URL::signedRoute('newsletter.unsubscribe', ['email' => (string) $email]);
    }

    public static function confirm(int $clientId): string
    {
        return URL::temporarySignedRoute('newsletter.confirm', now()->addDays(7), ['client' => $clientId]);
    }
}
