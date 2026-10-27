<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ConvertedProposal;
use App\Models\Expense;
use App\Permissions\PermissionService;
use App\Services\ConvertedProposalService;
use Illuminate\Http\Request;

/**
 * Cotações convertidas na versão mobile: consulta e mudança de estado do
 * pipeline (a mesma lógica do desktop, ConvertedProposalService). Pagamentos,
 * documentos e contrato editam-se no desktop.
 *
 * Ver: quem pode ver cotações convertidas vê todas; quem só vê cotações vê as
 * dos clientes de que é dono (como a área do angariador no desktop). Mudar o
 * estado exige editar cotações convertidas, como a rota update-status. As
 * verificações ficam aqui para não dependerem de PERMISSIONS_ENFORCE.
 */
class ConvertedProposalController extends Controller
{
    public function __construct(private ConvertedProposalService $service, private PermissionService $permissions)
    {
    }

    public function index(Request $request)
    {
        abort_unless($this->service->viewScope($request->user()), 403);

        $query = ConvertedProposal::with('client')->orderBy('created_at', 'desc');

        // Etapa do pipeline; "Cancelada" inclui a grafia antiga "Cancelado".
        $estado = $request->string('status')->toString();
        if (in_array($estado, ConvertedProposal::PIPELINE_STATUSES, true) || $estado === 'Cancelada') {
            $query->where(fn ($q) => match ($estado) {
                'Cancelada' => $q->whereIn('status', ConvertedProposal::CANCELLED_STATUSES),
                // Sem estado conta como Iniciada (como no kanban).
                'Iniciada' => $q->where('status', 'Iniciada')->orWhereNull('status'),
                default => $q->where('status', $estado),
            });
        } else {
            $estado = null;
        }

        // Pesquisa por cliente ou viatura (marca, modelo, versão, matrículas, VIN).
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('brand', 'like', "%{$search}%")
                    ->orWhere('modelCar', 'like', "%{$search}%")
                    ->orWhere('version', 'like', "%{$search}%")
                    ->orWhere('matricula_origem', 'like', "%{$search}%")
                    ->orWhere('matricula_destino', 'like', "%{$search}%")
                    ->orWhere('vin', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        $convertedProposals = $this->service->applyViewScope($query, $request->user())
            ->paginate(15)
            ->withQueryString();

        return view('admin.mobile.converted-proposals.index', [
            'convertedProposals' => $convertedProposals,
            'estado' => $estado,
            'statuses' => [...ConvertedProposal::PIPELINE_STATUSES, 'Cancelada'],
        ]);
    }

    public function show(Request $request, $id)
    {
        $convertedProposal = ConvertedProposal::with(['client', 'proposal', 'owner', 'v3Vehicle'])->findOrFail($id);
        $user = $request->user();
        abort_unless($this->service->canView($user, $convertedProposal), 403);

        $history = $convertedProposal->statusHistories()->orderBy('created_at', 'desc')->get();
        // Data em que cada etapa foi atingida (a primeira vez que entrou nela).
        $reachedAt = $history->sortBy('created_at')->groupBy('new_status')->map(fn ($h) => $h->first()->created_at);
        $reachedAt['Iniciada'] ??= $convertedProposal->created_at;

        $client = $convertedProposal->client;
        $proposal = $convertedProposal->proposal;

        return view('admin.mobile.converted-proposals.show', [
            'converted' => $convertedProposal,
            'history' => $history,
            'reachedAt' => $reachedAt,
            'statuses' => ConvertedProposal::PIPELINE_STATUSES,
            'canChangeStatus' => $this->service->canChangeStatus($user),
            // Custos, tranches e comissão da Izzycar: só quem edita a convertida no desktop (é aí que aparecem).
            'canSeeFinance' => $this->permissions->allows($user, 'converted-proposals', 'update'),
            // Comissão do angariador: como na área de Comissões (todas, ou a própria).
            'canSeeAngariadorCommission' => $convertedProposal->owner_id !== null
                && $this->permissions->allows($user, 'commissions', 'view', $convertedProposal),
            'canViewClient' => $client !== null && $this->permissions->allows($user, $client->is_lead ? 'leads' : 'clients', 'view', $client),
            'canViewProposal' => $proposal !== null && $this->permissions->allows($user, 'proposals', 'view', $proposal->setRelation('client', $client)),
            // Lançamentos automáticos desta importação (tranches, comissão, custos pagos): só para quem vê movimentos.
            'movementsCount' => $this->permissions->allows($user, 'movements', 'view')
                ? Expense::fromConvertedProposal($convertedProposal->id)->count()
                : null,
        ]);
    }

    public function status(Request $request, $id)
    {
        $convertedProposal = ConvertedProposal::with('client')->findOrFail($id);
        $user = $request->user();
        abort_unless($this->service->canView($user, $convertedProposal) && $this->service->canChangeStatus($user), 403);

        $request->validate($this->service->statusRules(), $this->service->statusMessages());

        $result = $this->service->changeStatus($convertedProposal, $request->status, $request->boolean('notify', true));

        if (! $result['changed']) {
            return redirect()->route('admin.mobile.converted-proposals.show', $convertedProposal->id)
                ->with('info', 'O estado já era este.');
        }

        return redirect()->route('admin.mobile.converted-proposals.show', $convertedProposal->id)
            ->with('success', "Estado: {$result['newStatus']}." . ($result['notified'] ? ' O cliente foi avisado por email.' : ''));
    }
}
