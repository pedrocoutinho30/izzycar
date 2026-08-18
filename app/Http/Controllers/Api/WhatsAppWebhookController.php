<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PreLead;
use App\Services\PhoneNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Webhook do WhatsApp Cloud API (Meta). Cria um PreLead pendente sempre que
 * chega uma mensagem de texto de um número que ainda não é Cliente/Lead.
 * Mensagens de estado (entregue/lido) não têm a chave "messages" e são
 * ignoradas — é assim que se filtram eventos que não são mensagens
 * recebidas de um cliente.
 */
class WhatsAppWebhookController extends Controller
{
    public function verify(Request $request)
    {
        // PHP converte automaticamente os pontos em "hub.mode" etc. para "_"
        // ao interpretar a query string, por isso os parâmetros chegam como
        // hub_mode / hub_verify_token / hub_challenge.
        if ($request->query('hub_mode') === 'subscribe'
            && $request->query('hub_verify_token') === config('services.whatsapp.verify_token')) {
            return response($request->query('hub_challenge'), 200);
        }

        return response('Forbidden', 403);
    }

    public function handle(Request $request, PhoneNumberService $phones)
    {
        try {
            foreach (data_get($request->all(), 'entry', []) as $entry) {
                foreach (data_get($entry, 'changes', []) as $change) {
                    $this->processChange(data_get($change, 'value', []), $phones);
                }
            }
        } catch (\Throwable $e) {
            Log::error('WhatsApp webhook: falha ao processar evento', ['error' => $e->getMessage()]);
        }

        // Responder sempre 200 (exceto falha de assinatura, tratada no middleware) —
        // a Meta desativa a subscrição do webhook após várias respostas não-2xx.
        return response()->json(['status' => 'ok']);
    }

    private function processChange(array $value, PhoneNumberService $phones): void
    {
        $messages = data_get($value, 'messages', []);

        if (empty($messages)) {
            return;
        }

        $contactsByWaId = collect(data_get($value, 'contacts', []))->keyBy('wa_id');

        foreach ($messages as $message) {
            if (($message['type'] ?? null) !== 'text') {
                continue;
            }

            $from = $message['from'] ?? null;
            $body = $message['text']['body'] ?? null;

            if (! $from || $body === null) {
                continue;
            }

            $e164 = $phones->normalizeE164FromWebhook($from);

            if ($phones->findExistingClient($e164)) {
                continue;
            }

            $profileName = data_get($contactsByWaId->get($from), 'profile.name');

            PreLead::firstOrCreate(
                ['phone' => $e164],
                ['name' => $profileName, 'message' => $body, 'status' => 'pendente']
            );

            Log::info('WhatsApp: PreLead criado/já existente', ['message_id' => $message['id'] ?? null]);
        }
    }
}
