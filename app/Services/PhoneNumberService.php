<?php

namespace App\Services;

use App\Models\Client;

/**
 * Normalização mínima de números de telefone para o fluxo de WhatsApp.
 * `clients.phone` nunca foi normalizado (formatos legados inconsistentes:
 * com/sem "+351", espaços, travessões), por isso a comparação usa só os
 * últimos 9 dígitos (todo o número móvel português tem 9 dígitos
 * significativos) em vez de depender de uma biblioteca de parsing completa.
 */
class PhoneNumberService
{
    public function normalizeE164FromWebhook(string $waId): string
    {
        return '+' . preg_replace('/\D/', '', $waId);
    }

    /**
     * Link wa.me (só dígitos). Os 9 dígitos portugueses gravados sem
     * indicativo levam o 351; "00" internacional passa a nada.
     */
    public function whatsappUrl(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }
        if ($digits === '') {
            return null;
        }
        if (strlen($digits) === 9) {
            $digits = '351' . $digits;
        }

        return 'https://wa.me/' . $digits;
    }

    /** Link tel: com dígitos e o "+" inicial, sem espaços nem travessões. */
    public function telUrl(?string $phone): ?string
    {
        $number = preg_replace('/[^\d+]/', '', trim((string) $phone));

        return $number === '' || $number === '+' ? null : 'tel:' . $number;
    }

    public function findExistingClient(string $phone): ?Client
    {
        return app(ClientMatcher::class)->find(null, $phone)?->client;
    }
}
