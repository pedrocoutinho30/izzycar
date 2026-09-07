<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Guarda/remove a subscrição de push notifications do browser do utilizador
 * autenticado (uma por dispositivo/instalação — o próprio endpoint identifica
 * o dispositivo). Usado pelo botão "Ativar Notificações" no topbar do BO.
 */
class PushSubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
        ]);

        $request->user()->updatePushSubscription(
            $validated['endpoint'],
            $validated['keys']['p256dh'],
            $validated['keys']['auth']
        );

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request)
    {
        $request->validate(['endpoint' => ['required', 'string']]);

        $request->user()->deletePushSubscription($request->input('endpoint'));

        return response()->json(['success' => true]);
    }
}
