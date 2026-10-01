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

    public function findExistingClient(string $phone): ?Client
    {
        return app(ClientMatcher::class)->find(null, $phone)?->client;
    }
}
