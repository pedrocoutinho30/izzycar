<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
}
