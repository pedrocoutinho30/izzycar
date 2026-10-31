<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterConfirmation;
use App\Mail\NewsletterUnsubscribeNotification;
use App\Models\Client;
use App\Services\ClientMatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NewsletterController extends Controller
{
    /**
     * Cancelamento: só com URL assinada (os links dos emails já o são).
     * Um link sem assinatura válida (emails antigos ou email escrito à mão)
     * não altera nada: mostra uma página 403 a explicar como proceder.
     */
    public function unsubscribe(Request $request)
    {
        if (!$request->hasValidSignature()) {
            return response()->view('newsletter.mensagem', [
                'titulo'   => 'Link inválido',
                'mensagem' => 'Este link de cancelamento não é válido ou foi alterado. Use o link "Deixar de subscrever" do email mais recente que recebeu ou escreva-nos para ' . (config('mail.from.address') ?: 'geral@izzycar.pt') . ' e tratamos do pedido.',
            ], 403);
        }

        $email = app(ClientMatcher::class)->normalizeEmail($request->query('email'));

        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $client = Client::where('email', $email)->first();

            // Só avisa a administração (e só regista) quando o consentimento mudou de facto.
            if ($client && $client->newsletter_consent) {
                $client->update(['newsletter_consent' => false]);
                Log::info('Newsletter consent atualizado para false', ['client_id' => $client->id]);

                $adminEmail = config('mail.from.address');

                if (!empty($adminEmail)) {
                    try {
                        Mail::to($adminEmail)->send(
                            new NewsletterUnsubscribeNotification($email, $client->name ?: 'N/A')
                        );
                    } catch (\Exception $e) {
                        Log::error('Erro ao enviar notificação de unsubscribe', [
                            'error'     => $e->getMessage(),
                            'client_id' => $client->id,
                        ]);
                    }
                }
            }
        }

        return response()->view('newsletter.unsubscribe');
    }

    /** Confirmação (double opt-in) a partir do link assinado enviado por email. */
    public function confirm(Request $request, int $client)
    {
        if (!$request->hasValidSignature()) {
            return response()->view('newsletter.mensagem', [
                'titulo'   => 'Link inválido ou expirado',
                'mensagem' => 'Este link de confirmação não é válido ou já expirou. Volte a subscrever a newsletter no site.',
            ], 403);
        }

        $model = Client::find($client);

        if ($model && !$model->newsletter_consent) {
            $model->update(['newsletter_consent' => true]);
            Log::info('Newsletter confirmada por email', ['client_id' => $model->id]);
        }

        return response()->view('newsletter.mensagem', [
            'titulo'   => 'Subscrição confirmada',
            'mensagem' => 'Obrigado! A sua subscrição da newsletter da Izzycar está confirmada.',
        ]);
    }

    /**
     * Subscrição do rodapé com confirmação por email. Nunca liga o consentimento
     * aqui: cria o contacto sem consentimento (ou deixa o existente como está) e
     * envia um link assinado. A resposta é sempre a mesma, para não revelar se o
     * email já existe.
     */
    public function subscribe(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'email' => ['required', 'email', 'max:255'],
                'name'  => ['nullable', 'string', 'max:255'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Email inválido.',
            ], 422);
        }

        $client = app(ClientMatcher::class)->find($data['email'], null)?->client;

        if (!$client) {
            $client = Client::create([
                'email'              => $data['email'],
                'name'               => $data['name'] ?? '',
                'newsletter_consent' => false,
                'origin'             => 'Subscrição Rodapé',
                'is_lead'            => true,
                'lead_source'        => 'newsletter_footer',
            ]);
        }

        // Quem já deu consentimento não recebe outro pedido.
        if (!$client->newsletter_consent && $client->email) {
            try {
                Mail::to($client->email)->send(new NewsletterConfirmation((string) $client->name, $client->id));
            } catch (\Exception $e) {
                Log::error('Erro ao enviar confirmação de newsletter', ['client_id' => $client->id, 'error' => $e->getMessage()]);
            }
        }

        Log::info('Newsletter: pedido de subscrição via rodapé', ['client_id' => $client->id]);

        return response()->json(['success' => true, 'message' => 'Enviámos-lhe um email para confirmar a subscrição.']);
    }
}
