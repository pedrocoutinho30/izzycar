<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Candidatura pública a angariador — o próprio preenche os seus dados,
 * mas a conta fica "pendente" até um admin a aprovar. Não é enviado
 * nenhum email de "definir password" neste momento (isso só acontece
 * quando o admin aprova) — em vez disso, avisa-se a administração de
 * que há uma nova candidatura para analisar.
 */
class AngariadorRegistrationController extends Controller
{
    public function create()
    {
        return view('public.angariador-register');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:30',
            'location' => 'nullable|string|max:255',
            'nif' => 'nullable|string|max:20',
            'iban' => 'nullable|string|max:40',
            'message' => 'nullable|string|max:2000',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'location' => $validated['location'] ?? null,
            'nif' => $validated['nif'] ?? null,
            'iban' => $validated['iban'] ?? null,
            'password' => Str::random(40),
            'status' => 'pendente',
        ]);

        $user->assignRole('angariador');

        Mail::raw(
            "Nova candidatura a angariador submetida através do site.\n\n"
                . "Nome: {$user->name} {$user->last_name}\n"
                . "Email: {$user->email}\n"
                . "Telefone: {$user->phone}\n"
                . ($user->location ? "Localização: {$user->location}\n" : '')
                . ($user->nif ? "NIF: {$user->nif}\n" : '')
                . ($user->iban ? "IBAN: {$user->iban}\n" : '')
                . ($validated['message'] ?? '' ? "\nMensagem:\n{$validated['message']}\n" : '')
                . "\nReveja e aprove (ou rejeite) esta candidatura em: " . route('admin.v2.angariadores.index'),
            function ($message) {
                $message->to('geral@izzycar.pt')
                    ->subject('Nova Candidatura a Angariador — Izzycar');
            }
        );

        return redirect()->route('public.angariador.register')
            ->with('success', 'Candidatura enviada com sucesso! A nossa equipa vai analisar o seu registo e entrará em contacto em breve.');
    }
}
