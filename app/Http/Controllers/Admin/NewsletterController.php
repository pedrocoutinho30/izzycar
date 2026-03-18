<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterMail;
use App\Mail\NewsletterUnsubscribeNotification;
use App\Models\Client;
use App\Models\NewsletterOffer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NewsletterController extends Controller
{
    public function unsubscribe(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);
        // Buscar cliente na base de dados
        $client = Client::query()
            ->where('email', $data['email'])
            ->first();

        // Se o cliente existir, atualizar newsletter_consent para false
        if ($client) {
            $client->update(['newsletter_consent' => false]);
            Log::info('Newsletter consent atualizado para false', [
                'client_id' => $client->id,
                'email' => $client->email
            ]);
        }

        // Enviar email ao admin
        $adminEmail = config('mail.from.address');

        if (!empty($adminEmail)) {
            try {
                Mail::to($adminEmail)->send(
                    new NewsletterUnsubscribeNotification(
                        $data['email'],
                        $client?->name ?? $data['name'] ?? 'N/A'
                    )
                );
                
                Log::info('Email de notificação de unsubscribe enviado ao admin', [
                    'email' => $data['email']
                ]);
            } catch (\Exception $e) {
                Log::error('Erro ao enviar email de notificação de unsubscribe', [
                    'error' => $e->getMessage(),
                    'email' => $data['email']
                ]);
            }
        }

        return response()
            ->view('newsletter.unsubscribe');
    }


}
