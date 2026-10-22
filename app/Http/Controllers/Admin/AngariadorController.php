<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\FormProposal;
use App\Models\LeadActivity;
use App\Models\Proposal;
use App\Services\AngariadorContractService;
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

    /** O próprio angariador abre o seu contrato assinado, se já foi carregado. */
    public function contrato()
    {
        return AngariadorContractService::respostaAssinado(Auth::user());
    }

    public function leads()
    {
        $leads = Client::where('owner_id', Auth::id())
            ->withCount('proposals')
            ->latest()
            ->get();

        return view('admin.v2.angariador.leads', compact('leads'));
    }

    public function storeLead(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:50',
            'origin'      => 'nullable|string|max:255',
            'lead_status' => 'nullable|in:nova,em_contacto,fria',
            'observation' => 'nullable|string|max:2000',
        ]);

        // Já existe um contacto com este email/telefone? Não se mostra de quem é
        // (pode ser de outro angariador) — a Izzycar trata do caso.
        if (app(\App\Services\ClientMatcher::class)->find($data['email'] ?? null, $data['phone'] ?? null)) {
            return back()->withInput()->withErrors(['email' => 'Já existe um contacto com este email ou telefone. Fale com a Izzycar para o associar à sua conta.']);
        }

        $lead = Client::create([
            ...$data,
            'is_lead'         => true,
            'lead_source'     => 'manual',
            'lead_status'     => $data['lead_status'] ?? 'nova',
            'origin'          => $data['origin'] ?? 'Manual Angariador',
            'owner_id'        => Auth::id(),
            'angariador_code' => Auth::user()->referral_code,
        ]);

        LeadActivity::log(
            $lead->id,
            'Lead criada manualmente',
            'Criada pelo angariador ' . (Auth::user()->name ?? '—') . '.',
            'bi-plus-circle-fill',
            'success'
        );

        return redirect()->route('admin.angariador.leads.show', $lead->id)
            ->with('success', 'Lead criada com sucesso.');
    }

    public function leadShow(Client $client)
    {
        abort_if($client->owner_id !== Auth::id(), 403);

        $client->load(['proposals', 'convertedProposals.proposal', 'activities.user']);

        // Pedidos de importação (formulários) desta lead; sem as oportunidades,
        // que levam a análise interna de margens.
        $formProposals = FormProposal::where('client_id', $client->id)->latest()->get();

        return view('admin.v2.angariador.lead-show', compact('client', 'formProposals'));
    }

    public function kanban()
    {
        $columns = [
            'nova'        => ['label' => 'Nova',        'color' => 'success',   'icon' => 'bi-circle-fill'],
            'em_contacto' => ['label' => 'Em Contacto', 'color' => 'info',      'icon' => 'bi-telephone-fill'],
            'qualificada' => ['label' => 'Qualificada', 'color' => 'primary',   'icon' => 'bi-patch-check-fill'],
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
            'qualificada' => 'Qualificada',
            'fria'        => 'Fria',
            'perdida'     => 'Perdida',
        ];
        $statusColors = ['nova' => 'success', 'em_contacto' => 'info', 'qualificada' => 'primary', 'fria' => 'secondary', 'perdida' => 'danger'];

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

    public function cotacoes()
    {
        $cotacoes = Proposal::whereHas('client', fn ($q) => $q->where('owner_id', Auth::id()))
            ->with('client')
            ->latest()
            ->get();

        return view('admin.v2.angariador.cotacoes', compact('cotacoes'));
    }

    /** Cotações convertidas dos clientes deste angariador (só valores finais e estados). */
    public function convertidas()
    {
        $convertidas = ConvertedProposal::whereHas('client', fn ($q) => $q->where('owner_id', Auth::id()))
            ->with(['client', 'proposal'])
            ->latest()
            ->get();

        return view('admin.v2.angariador.convertidas', compact('convertidas'));
    }

    public function convertidaShow(ConvertedProposal $convertedProposal)
    {
        $convertedProposal->load(['client', 'proposal', 'statusHistories']);

        // 404 (e não 403) para não revelar que o registo existe.
        abort_unless($convertedProposal->client && $convertedProposal->client->owner_id === Auth::id(), 404);

        // Data em que cada etapa foi atingida (a primeira vez que entrou nela).
        $datas = $convertedProposal->statusHistories
            ->sortBy('created_at')
            ->groupBy('new_status')
            ->map(fn ($h) => $h->first()->created_at);
        $datas['Iniciada'] = $convertedProposal->created_at;

        return view('admin.v2.angariador.convertida-show', [
            'converted' => $convertedProposal,
            'etapas' => ConvertedProposal::PIPELINE_STATUSES,
            'datas' => $datas,
        ]);
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
}
