<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\FormProposal;
use App\Models\LeadActivity;
use App\Models\Proposal;
use App\Services\AngariadorMetricsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Área do angariador — cada angariador só vê as leads de que é proprietário
 * (clients.owner_id), as propostas (V1) enviadas a essas leads e as
 * comissões das cotações convertidas em que é o angariador
 * (converted_proposals.owner_id). Nunca vê os detalhes internos
 * (margens/custos) do processo de conversão.
 */
class AngariadorController extends Controller
{
    public function dashboard()
    {
        $metrics = (new AngariadorMetricsService())->forUser(Auth::id());

        return view('admin.v2.angariador.dashboard', $metrics);
    }

    public function leads()
    {
        $leads = Client::where('owner_id', Auth::id())
            ->withCount('proposals')
            ->latest()
            ->get();

        return view('admin.v2.angariador.leads', compact('leads'));
    }

    public function leadShow(Client $client)
    {
        abort_if($client->owner_id !== Auth::id(), 403);

        $client->load(['proposals', 'activities.user']);

        return view('admin.v2.angariador.lead-show', compact('client'));
    }

    public function kanban()
    {
        $columns = [
            'nova'        => ['label' => 'Nova',        'color' => 'success',   'icon' => 'bi-circle-fill'],
            'em_contacto' => ['label' => 'Em Contacto', 'color' => 'info',      'icon' => 'bi-telephone-fill'],
            'fria'        => ['label' => 'Fria',        'color' => 'secondary', 'icon' => 'bi-snow'],
            'perdida'     => ['label' => 'Perdida',     'color' => 'danger',    'icon' => 'bi-x-circle-fill'],
        ];

        $leads = Client::where('owner_id', Auth::id())
            ->with(['activities' => fn ($q) => $q->latest()->limit(1)])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(fn ($l) => $l->lead_status ?? 'nova');

        return view('admin.v2.angariador.leads-kanban', compact('columns', 'leads'));
    }

    public function updateStatus(Request $request, Client $client)
    {
        abort_if($client->owner_id !== Auth::id(), 403);

        $request->validate(['status' => 'required|in:nova,em_contacto,fria,perdida']);

        $oldStatus = $client->lead_status ?? 'nova';
        $client->update(['lead_status' => $request->status]);

        $statusLabels = [
            'nova'        => 'Nova',
            'em_contacto' => 'Em Contacto',
            'fria'        => 'Fria',
            'perdida'     => 'Perdida',
        ];
        $statusColors = ['nova' => 'success', 'em_contacto' => 'info', 'fria' => 'secondary', 'perdida' => 'danger'];

        LeadActivity::log(
            $client->id,
            'Estado alterado: ' . ($statusLabels[$oldStatus] ?? $oldStatus) . ' → ' . ($statusLabels[$request->status] ?? $request->status),
            '',
            'bi-arrow-repeat',
            $statusColors[$request->status] ?? 'secondary'
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Estado da lead atualizado.');
    }

    public function storeActivity(Request $request, Client $client)
    {
        abort_if($client->owner_id !== Auth::id(), 403);

        $request->validate([
            'type'  => 'required|in:note,call,email,whatsapp,facebook,meeting',
            'title' => 'required|string|max:255',
            'body'  => 'nullable|string|max:2000',
        ]);

        LeadActivity::logManual($client->id, $request->type, $request->title, $request->body ?? '');

        return back()->with('success', 'Actividade registada.');
    }

    public function saveFollowup(Request $request, Client $client)
    {
        abort_if($client->owner_id !== Auth::id(), 403);

        $request->validate([
            'next_followup_at' => 'required|date|after:now',
            'followup_note'    => 'nullable|string|max:255',
        ]);

        $client->update([
            'next_followup_at' => $request->next_followup_at,
            'followup_note'    => $request->followup_note,
        ]);

        LeadActivity::log(
            $client->id,
            'Follow-up agendado para ' . \Carbon\Carbon::parse($request->next_followup_at)->format('d/m/Y H:i'),
            $request->followup_note ?? '',
            'bi-alarm-fill',
            'warning'
        );

        return back()->with('success', 'Follow-up agendado.');
    }

    public function propostas()
    {
        $propostas = Proposal::whereHas('client', fn ($q) => $q->where('owner_id', Auth::id()))
            ->with('client')
            ->latest()
            ->get();

        return view('admin.v2.angariador.propostas', compact('propostas'));
    }

    public function comissoes()
    {
        $convertedProposals = ConvertedProposal::with(['client', 'statusHistories'])
            ->where('owner_id', Auth::id())
            ->latest()
            ->get();

        [$recebido, $pendente] = ConvertedProposal::commissionTotals($convertedProposals);

        return view('admin.v2.angariador.comissoes', [
            'convertedProposals' => $convertedProposals,
            'comissaoRecebida' => $recebido,
            'comissaoPendente' => $pendente,
        ]);
    }

    public function formularios()
    {
        $ownedClientIds = Client::where('owner_id', Auth::id())->pluck('id');

        $formularios = FormProposal::whereIn('client_id', $ownedClientIds)
            ->orderByDesc('created_at')
            ->get();

        return view('admin.v2.angariador.formularios', compact('formularios'));
    }
}
