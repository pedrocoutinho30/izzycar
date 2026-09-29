<?php

namespace App\Services;

use App\Enums\ChecklistItemStatus;
use App\Enums\ContactMethod;
use App\Enums\ContactStatus;
use App\Enums\ContactType;
use App\Enums\OpportunityStatus;
use App\Models\FormProposal;
use App\Models\ImportOpportunity;
use App\Models\ImportOpportunityChecklistEntry;
use App\Models\ImportOpportunityContact;
use App\Models\OpportunityChecklist;
use App\Models\Proposal;
use App\Models\User;
use App\Support\ChecklistProgress;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Regras das Oportunidades de um Pedido de Importação: checklists aplicáveis,
 * progresso, e efeitos de registar contactos no estado da oportunidade.
 */
class ImportOpportunityService
{
    /** @var Collection<int, OpportunityChecklist>|null */
    private ?Collection $checklists = null;

    /**
     * Checklists ativas (com itens ativos) — carregadas uma vez por pedido
     * HTTP, porque a grelha de cartões calcula o progresso de dezenas de
     * oportunidades.
     *
     * @return Collection<int, OpportunityChecklist>
     */
    public function allChecklists(): Collection
    {
        return $this->checklists ??= OpportunityChecklist::active()
            ->with(['items' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();
    }

    /** @return Collection<int, OpportunityChecklist> */
    public function checklistsFor(ImportOpportunity $opportunity): Collection
    {
        return $this->allChecklists()
            ->filter(fn (OpportunityChecklist $checklist) => $checklist->appliesToFuel($opportunity->fuel?->value))
            ->values();
    }

    /**
     * Estado de cada item aplicável, indexado pelo id do item (itens sem
     * registo contam como "Por confirmar").
     *
     * @return array<int, ChecklistItemStatus>
     */
    public function checklistStatuses(ImportOpportunity $opportunity): array
    {
        $entries = $opportunity->relationLoaded('checklistEntries')
            ? $opportunity->checklistEntries
            : $opportunity->checklistEntries()->get();

        $byItem = $entries->keyBy('opportunity_checklist_item_id');
        $statuses = [];

        foreach ($this->checklistsFor($opportunity) as $checklist) {
            foreach ($checklist->items as $item) {
                $statuses[$item->id] = $byItem->get($item->id)?->status ?? ChecklistItemStatus::Pending;
            }
        }

        return $statuses;
    }

    public function progress(ImportOpportunity $opportunity): ChecklistProgress
    {
        $statuses = $this->checklistStatuses($opportunity);
        $labels = $this->checklistsFor($opportunity)->flatMap->items->pluck('label', 'id');

        $pendingLabels = collect($statuses)
            ->filter(fn (ChecklistItemStatus $status) => $status === ChecklistItemStatus::Pending)
            ->keys()
            ->map(fn (int $itemId) => $labels[$itemId])
            ->values()
            ->all();

        return new ChecklistProgress(
            total: count($statuses),
            confirmed: count(array_filter($statuses, fn ($s) => $s === ChecklistItemStatus::Confirmed)),
            notApplicable: count(array_filter($statuses, fn ($s) => $s === ChecklistItemStatus::NotApplicable)),
            pendingLabels: $pendingLabels,
        );
    }

    public function create(FormProposal $formProposal, array $data, ?UploadedFile $photo, ?User $user): ImportOpportunity
    {
        $opportunity = $formProposal->opportunities()->create($data + [
            'created_by' => $user?->id,
        ]);

        if ($photo) {
            $this->replacePhoto($opportunity, $photo);
        }

        return $opportunity;
    }

    public function update(ImportOpportunity $opportunity, array $data, ?UploadedFile $photo, bool $removePhoto = false): ImportOpportunity
    {
        $opportunity->update($data);

        if ($photo) {
            $this->replacePhoto($opportunity, $photo);
        } elseif ($removePhoto) {
            $this->deletePhoto($opportunity);
            $opportunity->update(['photo_path' => null]);
        }

        return $opportunity;
    }

    public function delete(ImportOpportunity $opportunity): void
    {
        $this->deletePhoto($opportunity);
        $opportunity->delete();
    }

    /**
     * Fotos das oportunidades de um pedido — a FK elimina as linhas em
     * cascata, mas os ficheiros no disco têm de ser removidos à parte.
     */
    public function deletePhotosFor(FormProposal $formProposal): void
    {
        $paths = $formProposal->opportunities()->whereNotNull('photo_path')->pluck('photo_path')->all();

        if ($paths) {
            Storage::disk('public')->delete($paths);
        }
    }

    public function setChecklistStatus(ImportOpportunity $opportunity, int $itemId, ChecklistItemStatus $status, ?User $user): void
    {
        $applicable = $this->checklistsFor($opportunity)->flatMap->items->pluck('id');

        abort_unless($applicable->contains($itemId), 422, 'Item de checklist não aplicável a esta oportunidade.');

        ImportOpportunityChecklistEntry::updateOrCreate(
            ['import_opportunity_id' => $opportunity->id, 'opportunity_checklist_item_id' => $itemId],
            ['status' => $status, 'updated_by' => $user?->id]
        );

        $opportunity->unsetRelation('checklistEntries');
    }

    /**
     * Regista um contacto no histórico e reflete-o no acompanhamento: método e
     * data do último contacto, e avança os estados que ainda estejam no início
     * (nunca faz regredir um estado que o utilizador já tenha avançado).
     */
    public function logContact(ImportOpportunity $opportunity, array $data, ?User $user): ImportOpportunityContact
    {
        return DB::transaction(function () use ($opportunity, $data, $user) {
            $contact = $opportunity->contacts()->create($data + ['user_id' => $user?->id]);

            if (! $contact->type->isOutreach()) {
                return $contact;
            }

            $updates = [];

            if (! $opportunity->last_contacted_at || $contact->contacted_at->gte($opportunity->last_contacted_at)) {
                $updates['last_contacted_at'] = $contact->contacted_at;
                $updates['contact_method'] = $contact->method;
            }

            $updates['contact_status'] = match (true) {
                $contact->type === ContactType::Received
                    && in_array($opportunity->contact_status, [ContactStatus::NotContacted, ContactStatus::Contacted], true) => ContactStatus::Replied,
                $opportunity->contact_status === ContactStatus::NotContacted => ContactStatus::Contacted,
                default => $opportunity->contact_status,
            };

            if ($opportunity->status === OpportunityStatus::ToContact) {
                $updates['status'] = OpportunityStatus::Contacted;
            }

            $opportunity->update($updates);

            return $contact;
        });
    }

    /** Remove um registo e recalcula a data/método do último contacto. */
    public function deleteContact(ImportOpportunity $opportunity, ImportOpportunityContact $contact): void
    {
        DB::transaction(function () use ($opportunity, $contact) {
            $contact->delete();

            $latest = $opportunity->contacts()
                ->where('type', '!=', ContactType::Note->value)
                ->first();

            $opportunity->update([
                'last_contacted_at' => $latest?->contacted_at,
                'contact_method' => $latest?->method ?? $opportunity->contact_method,
            ]);
        });
    }

    /**
     * Liga a cotação criada a partir da oportunidade: passa-a a "Selecionado"
     * e deixa uma nota no histórico de contactos.
     */
    public function linkProposal(ImportOpportunity $opportunity, Proposal $proposal, ?User $user): void
    {
        DB::transaction(function () use ($opportunity, $proposal, $user) {
            $opportunity->update([
                'proposal_id' => $proposal->id,
                'status' => OpportunityStatus::Selected,
            ]);

            $opportunity->contacts()->create([
                'contacted_at' => now(),
                'method' => ContactMethod::Other,
                'type' => ContactType::Note,
                'message' => "Cotação #{$proposal->id} criada a partir desta oportunidade.",
                'user_id' => $user?->id,
            ]);
        });
    }

    /**
     * Foto da oportunidade como UploadedFile, para passar pelo mesmo
     * processamento de imagem das cotações.
     */
    public function photoAsUpload(ImportOpportunity $opportunity): ?UploadedFile
    {
        $disk = Storage::disk('public');

        if (! $opportunity->photo_path || ! $disk->exists($opportunity->photo_path)) {
            return null;
        }

        return new UploadedFile(
            $disk->path($opportunity->photo_path),
            basename($opportunity->photo_path),
            $disk->mimeType($opportunity->photo_path),
            null,
            true
        );
    }

    private function replacePhoto(ImportOpportunity $opportunity, UploadedFile $photo): void
    {
        $this->deletePhoto($opportunity);

        $opportunity->update([
            'photo_path' => $photo->store('import-opportunities/' . $opportunity->form_proposal_id, 'public'),
        ]);
    }

    private function deletePhoto(ImportOpportunity $opportunity): void
    {
        if ($opportunity->photo_path) {
            Storage::disk('public')->delete($opportunity->photo_path);
        }
    }
}
