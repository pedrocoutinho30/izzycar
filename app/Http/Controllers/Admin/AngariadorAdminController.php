<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SetPasswordMail;
use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\User;
use App\Services\AngariadorMetricsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;

/**
 * Administração de angariadores: listagem+métricas, detalhe (sem
 * impersonation), impersonation reversível e ledger de comissões
 * cross-angariador com alerta de atraso (>24h após entrega sem pagamento).
 */
class AngariadorAdminController extends Controller
{
    public function index(Request $request)
    {
        $service = new AngariadorMetricsService();
        $rows = $service->forAllAngariadores($request->date_from, $request->date_to);
        $totals = $service->totals($rows);

        $pending = User::role('angariador')->where('status', 'pendente')->orderBy('created_at')->get();

        return view('admin.v2.angariadores.index', compact('rows', 'totals', 'pending'));
    }

    /**
     * Aprova uma candidatura de angariador — a conta passa a "aprovado" e só
     * agora é enviado o email de "definir password" (o registo público nunca
     * o envia, para não dar acesso antes de a administração validar o caso).
     * Se ainda não tiver um código de angariador, é gerado automaticamente
     * a partir do nome.
     */
    public function approve(User $user)
    {
        abort_unless($user->hasRole('angariador'), 404);

        $user->update([
            'status' => 'aprovado',
            'referral_code' => $user->referral_code
                ?? User::generateUniqueReferralCode($user->name, $user->last_name),
        ]);

        $token = Password::broker('setup')->createToken($user);
        $url = route('password.setup', ['token' => $token, 'email' => $user->email]);
        Mail::to($user->email)->send(new SetPasswordMail($user, $url));

        return back()->with('success', 'Candidatura aprovada. Foi enviado um email para o angariador definir a password.');
    }

    public function reject(User $user)
    {
        abort_unless($user->hasRole('angariador'), 404);

        $user->update(['status' => 'rejeitado']);

        return back()->with('success', 'Candidatura rejeitada.');
    }

    public function show(User $user, Request $request)
    {
        abort_unless($user->hasRole('angariador'), 404);

        $metrics = (new AngariadorMetricsService())->forUser($user->id, $request->date_from, $request->date_to);

        $leads = Client::where('owner_id', $user->id)
            ->with(['proposals', 'formProposals'])
            ->latest()
            ->get();

        return view('admin.v2.angariadores.show', array_merge($metrics, [
            'angariador' => $user,
            'leads' => $leads,
        ]));
    }

    public function impersonate(User $user)
    {
        abort_unless($user->hasRole('angariador'), 404);

        session()->put('impersonator_id', Auth::id());
        Auth::login($user);

        return redirect()->route('admin.angariador.dashboard')
            ->with('success', 'Está agora a visualizar a aplicação como ' . $user->name . '.');
    }

    public function stopImpersonating()
    {
        $originalId = session()->pull('impersonator_id');

        abort_unless($originalId, 403);

        Auth::loginUsingId($originalId);

        return redirect()->route('admin.v2.angariadores.index')
            ->with('success', 'Deixou de visualizar como angariador.');
    }

    public function comissoes(Request $request)
    {
        $query = ConvertedProposal::with(['client', 'owner', 'statusHistories'])
            ->whereNotNull('owner_id');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }
        if ($request->filled('status')) {
            $query->where('comissao_paga', $request->status === 'pago');
        }

        $convertedProposals = $query->latest()->get();
        [$recebido, $pendente] = ConvertedProposal::commissionTotals($convertedProposals);
        $angariadores = User::role('angariador')->orderBy('name')->get();

        return view('admin.v2.angariadores.comissoes', [
            'convertedProposals' => $convertedProposals,
            'comissaoRecebida' => $recebido,
            'comissaoPendente' => $pendente,
            'angariadores' => $angariadores,
        ]);
    }

    public function togglePaid(Request $request, ConvertedProposal $proposal)
    {
        if ($proposal->comissao_paga) {
            $proposal->update(['comissao_paga' => false, 'comissao_paga_em' => null]);
            return back()->with('success', 'Comissão marcada como pendente.');
        }

        $proposal->update([
            'comissao_paga' => true,
            'comissao_paga_em' => $request->input('paid_at', now()->toDateString()),
        ]);

        return back()->with('success', 'Comissão marcada como paga.');
    }

    /**
     * Anexa (ou substitui) o comprovativo de transferência desta comissão —
     * independente do estado pago/pendente, para poder ser carregado em
     * qualquer momento diretamente na linha da comissão.
     */
    public function uploadReceipt(Request $request, ConvertedProposal $proposal)
    {
        $request->validate([
            'comprovativo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        if ($proposal->comprovativo_pagamento) {
            Storage::disk('public')->delete($proposal->comprovativo_pagamento);
        }

        $proposal->update([
            'comprovativo_pagamento' => $request->file('comprovativo')->store('comissoes/comprovativos', 'public'),
        ]);

        return back()->with('success', 'Comprovativo anexado.');
    }
}
