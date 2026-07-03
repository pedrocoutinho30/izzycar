<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterMail;
use App\Mail\NewsletterUnsubscribeNotification;
use App\Models\Client;
use App\Models\NewsletterOffer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NewsletterController extends Controller
{
    public function unsubscribe(Request $request)
    {
        $data = $request->validate([
            'email' => ['nullable', 'email'],
            'name'  => ['nullable', 'string', 'max:255'],
        ]);

        $email = $data['email'] ?? null;

        if ($email) {
            $client = Client::where('email', $email)->first();

            if ($client) {
                $client->update(['newsletter_consent' => false]);
                Log::info('Newsletter consent atualizado para false', [
                    'client_id' => $client->id,
                    'email'     => $client->email,
                ]);
            }

            $adminEmail = config('mail.from.address');

            if (!empty($adminEmail)) {
                try {
                    Mail::to($adminEmail)->send(
                        new NewsletterUnsubscribeNotification(
                            $email,
                            $client?->name ?? $data['name'] ?? 'N/A'
                        )
                    );
                    Log::info('Email de notificação de unsubscribe enviado ao admin', ['email' => $email]);
                } catch (\Exception $e) {
                    Log::error('Erro ao enviar notificação de unsubscribe', [
                        'error' => $e->getMessage(),
                        'email' => $email,
                    ]);
                }
            }
        }

        return response()->view('newsletter.unsubscribe');
    }

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

        $client = Client::where('email', $data['email'])->first();

        if ($client) {
            $client->update(['newsletter_consent' => true]);
        } else {
            Client::create([
                'email'              => $data['email'],
                'name'               => $data['name'] ?? '',
                'newsletter_consent' => true,
                'origin'             => 'Subscrição Rodapé',
                'is_lead'            => true,
                'lead_source'        => 'newsletter_footer',
            ]);
        }

        Log::info('Newsletter subscrita via rodapé', ['email' => $data['email']]);

        return response()->json(['success' => true, 'message' => 'Subscrito com sucesso!']);
    }
}
