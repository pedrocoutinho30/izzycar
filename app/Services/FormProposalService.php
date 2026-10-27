<?php

namespace App\Services;

use App\Enums\ChecklistItemStatus;
use App\Enums\OpportunityStatus;
use App\Models\Client;
use App\Models\FormProposal;
use App\Models\ImportOpportunity;
use App\Models\LeadActivity;
use App\Models\User;
use App\Support\VehicleCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

/**
 * Regras dos pedidos de formulário (pedidos de importação) e das ações das
 * oportunidades que viviam nos controllers do desktop, partilhadas pelo
 * backoffice desktop (FormProposalV2Controller, ImportOpportunityController)
 * e pelo mobile. Mensagens e textos da timeline são os de sempre.
 */
class FormProposalService
{
    /** Estados que se escolhem à mão ("convertido" é automático). */
    public const MANUAL_STATUSES = ['novo', 'em_analise', 'rejeitado', 'arquivado'];

    /** Lista com os filtros do ecrã (estado exato e pesquisa), mais recentes primeiro. */
    public function query(array $filters): Builder
    {
        $query = FormProposal::withCount('opportunities')->orderBy('created_at', 'desc');

        if (filled($filters['status'] ?? null)) {
            $query->where('status', $filters['status']);
        }

        if (filled($filters['search'] ?? null)) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    /** Validação do pedido manual (criado na lead/cliente). */
    public function manualRules(?string $brandInput): array
    {
        return ['client_id' => 'required|integer|exists:clients,id'] + $this->vehicleRules($brandInput);
    }

    /** Campos do pedido editáveis no backoffice (manual ou do site). */
    public function vehicleRules(?string $brandInput, ?FormProposal $current = null): array
    {
        // Marca e modelo vêm dos selects do catálogo (valores antigos do site
        // fora do catálogo só são aceites se não mudarem).
        [$brandRule, $modelRule] = VehicleCatalog::rules($brandInput, $current?->brand, $current?->model);

        return [
            'title' => 'nullable|string|max:120',
            'brand' => ['nullable', 'string', 'max:100', $brandRule],
            'model' => ['nullable', 'string', 'max:100', $modelRule],
            'version' => 'nullable|string|max:255',
            'fuel' => 'nullable|string|max:50',
            'year_min' => 'nullable|integer|min:1950|max:' . (now()->year + 1),
            'km_max' => 'nullable|integer|min:0|max:2000000',
            'budget' => 'nullable|numeric|min:0|max:10000000',
            'message' => 'nullable|string|max:5000',
        ];
    }

    public function vehicleMessages(): array
    {
        return [
            'year_min.integer' => 'O ano tem de ser um número.',
            'km_max.integer' => 'Os quilómetros têm de ser um número inteiro.',
            'budget.numeric' => 'O orçamento tem de ser um número.',
        ];
    }

    /**
     * Pedido manual, criado na lead/cliente — serve para agrupar oportunidades
     * quando o cliente não preencheu o formulário do site (ex. cliente antigo
     * que quer um segundo carro). $data já validado com manualRules().
     */
    public function createManual(array $data, ?User $by): FormProposal
    {
        $client = Client::findOrFail($data['client_id']);

        $formProposal = FormProposal::create(collect($data)->except('client_id')->all() + [
            'client_id' => $client->id,
            // name/email/phone são obrigatórios na tabela (vêm do formulário do site).
            'name' => $client->name,
            'email' => $client->email ?? '',
            'phone' => $client->phone ?? '',
            'origin' => FormProposal::ORIGIN_MANUAL,
            'status' => 'em_analise',
            'created_by' => $by?->id,
            'angariador_code' => $client->angariador_code,
        ]);

        LeadActivity::log(
            $client->id,
            'Pedido de importação criado',
            "Pedido \"{$formProposal->label}\" criado manualmente no backoffice.",
            'bi-envelope-plus',
            'primary'
        );

        return $formProposal;
    }

    public function statusRules(): array
    {
        // "convertido" não se escolhe à mão: é posto automaticamente quando uma
        // cotação do pedido é aceite (ProposalAcceptanceService).
        return ['status' => 'required|in:' . implode(',', self::MANUAL_STATUSES)];
    }

    public function statusMessages(): array
    {
        return ['status.in' => '"Convertido" é automático — fica assim quando o cliente aceita uma cotação do pedido.'];
    }

    public function changeStatus(FormProposal $formProposal, string $status): void
    {
        $formProposal->update(['status' => $status]);
    }

    // ── Oportunidades ────────────────────────────────────────────────

    public function opportunityStatusRules(): array
    {
        return ['status' => ['required', Rule::enum(OpportunityStatus::class)]];
    }

    public function opportunityStatusMessages(): array
    {
        return ['status.required' => 'Escolha um estado.', 'status.enum' => 'Estado inválido.'];
    }

    /** Qualquer valor do enum é aceite: não há máquina de estados. */
    public function changeOpportunityStatus(ImportOpportunity $opportunity, string $status): OpportunityStatus
    {
        $opportunity->update(['status' => $status]);

        return $opportunity->status;
    }

    public function checklistRules(): array
    {
        return [
            'item_id' => 'required|integer',
            'status' => ['required', Rule::enum(ChecklistItemStatus::class)],
        ];
    }

    public function checklistMessages(): array
    {
        return ['status.enum' => 'Estado inválido.'];
    }

    public function notesRules(): array
    {
        return ['notes' => 'nullable|string|max:20000'];
    }

    public function notesMessages(): array
    {
        return ['notes.max' => 'As notas são demasiado longas.'];
    }

    /** $data validado com notesRules(): sem "notes" no pedido, nada muda. */
    public function saveOpportunityNotes(ImportOpportunity $opportunity, array $data): void
    {
        $opportunity->update($data);
    }
}
