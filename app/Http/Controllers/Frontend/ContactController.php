<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Client;
use App\Models\LeadActivity;
use App\Services\ClientMatcher;
use App\Models\Brand;

class ContactController extends Controller
{

    public function send(Request $request)
    {


        // Validate the request data
        $validated =  $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'email' => 'nullable|email|max:255',
            'message' => 'required|string|max:500',
            'url' => 'required|url',
        ]);

        // Regra única de duplicados; um contacto novo é uma lead (antes ficava
        // como cliente sem origem e nunca aparecia no funil).
        $matcher = app(ClientMatcher::class);
        $match = $matcher->find($validated['email'] ?? null, $validated['phone']);

        if ($match) {
            $matcher->complete($match->client, $validated);
            $matcher->logMatch($match, $validated['name'], 'Pedido de contacto');
            $client = $match->client;
        } else {
            $client = Client::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'origin' => 'Site',
                'is_lead' => true,
                'lead_source' => 'contacto',
            ]);
        }

        LeadActivity::log($client->id, 'Pedido de contacto sobre uma viatura', "Mensagem: {$validated['message']} — Anúncio: {$validated['url']}", 'bi-chat-dots-fill', 'primary');

        // Enviar email simples
        Mail::raw(
            "Novo pedido de contacto:\n\n" .
                "Nome: {$validated['name']}\n" .
                "Telefone: {$validated['phone']}\n" .
                "Mensagem: {$validated['message']}\n" .
                "Anúncio: {$validated['url']}",
            function ($mail) {
                $mail->to('geral@izzycar.pt')
                    ->subject('Pedido de contacto - Izzycar');
            }
        );

        return back()->with('success', 'O seu pedido foi enviado com sucesso!');
    }


    public function importForm()
    {

        $brands = Brand::with(['models' => function ($query) {
            $query->orderBy('name');
        }])->get();
        return view('frontend.import-form-page', compact('brands'));
    }
}
