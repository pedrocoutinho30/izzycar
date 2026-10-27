<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\FormProposal;
use App\Models\ImportOpportunity;
use App\Models\LeadActivity;
use App\Models\Proposal;
use App\Models\ProposalAttributeValue;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Criar, editar e mudar o estado das cotações — a lógica que vivia no
 * ProposalV2Controller, partilhada pelo desktop e pelo mobile. O estado muda
 * sempre por ProposalAcceptanceService (changeStatus/accept). Mensagens e
 * textos da timeline são os de sempre.
 */
class ProposalService
{
    /** Estados aceites nos formulários e na mudança rápida de estado. */
    public const STATUSES = ['Pendente', 'Aprovada', 'Reprovada', 'Enviado', 'Sem resposta'];

    /** Combustíveis da cotação (ver docs/cotacoes.md e VehicleFuel::proposalLabel). */
    public const FUELS = ['Gasolina', 'Diesel', 'Gasolina (HEV)', 'Diesel (HEV)', 'Híbrido Plug-in/Gasolina', 'Híbrido Plug-in/Diesel', 'Elétrico'];

    /** Custos por omissão de uma cotação nova. */
    public const DEFAULT_COSTS = [
        'transport_cost'             => 1350,
        'ipo_cost'                   => 100,
        'imt_cost'                   => 45,
        'registration_cost'          => 65,
        'isv_cost'                   => 0,
        'license_plate_cost'         => 20,
        'inspection_commission_cost' => 350,
        'commission_cost'            => 615,
        'iuc_cost'                   => 0,
    ];

    /** Cores da timeline para cada estado. */
    private const STATUS_COLORS = [
        'Pendente'     => 'secondary',
        'Enviado'      => 'primary',
        'Aprovada'     => 'success',
        'Reprovada'    => 'danger',
        'Sem resposta' => 'warning',
    ];

    public function __construct(private ProposalAcceptanceService $acceptance, private ImportOpportunityService $opportunities)
    {
    }

    /** Validação da criação (inclui as ligações ao pedido e à oportunidade). */
    public function rules(): array
    {
        return [
            'form_proposal_id' => 'nullable|exists:form_proposals,id',
            'import_opportunity_id' => 'nullable|exists:import_opportunities,id',
        ] + $this->updateRules();
    }

    /** Validação da edição (as mesmas regras, sem as ligações). */
    public function updateRules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'version' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'mileage' => 'nullable|integer|min:0',
            'engine_capacity' => 'nullable|numeric|min:0',
            'co2' => 'nullable|numeric|min:0',
            'fuel' => 'nullable|in:' . implode(',', self::FUELS),
            'value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'url' => 'nullable|url',
            'status' => 'nullable|in:' . implode(',', self::STATUSES),
            'transport_cost' => 'required|numeric|min:0',
            'ipo_cost' => 'nullable|numeric|min:0',
            'imt_cost' => 'nullable|numeric|min:0',
            'registration_cost' => 'nullable|numeric|min:0',
            'isv_cost' => 'nullable|numeric|min:0',
            'license_plate_cost' => 'nullable|numeric|min:0',
            'inspection_commission_cost' => 'nullable|numeric|min:0',
            'commission_cost' => 'nullable|numeric|min:0',
            'proposed_car_mileage' => 'nullable|integer|min:0',
            'proposed_car_year_month' => 'nullable|string',
            'proposed_car_value' => 'nullable|numeric|min:0',
            'proposed_car_notes' => 'nullable|string',
            'proposed_car_features' => 'nullable|string',
            'image' => 'nullable|image|mimes:webp,jpeg,png,jpg,gif,svg,avif|max:16000',
            'other_links' => 'nullable|array',
            'iuc_cost' => 'nullable|numeric|min:0',
        ];
    }

    /** Mudança rápida de estado (lista, kanban, mobile). */
    public function statusRules(): array
    {
        return [
            'status' => 'required|string|in:' . implode(',', self::STATUSES),
            // Só conta ao passar a "Enviado"; sem o campo, avisa-se como sempre.
            'notify' => 'nullable|boolean',
        ];
    }

    /**
     * Valores iniciais do formulário de cotação: a partir de uma oportunidade
     * (o carro encontrado), de um pedido de formulário ou só do cliente.
     * $brands: catálogo já carregado (marca/modelo da oportunidade são texto
     * livre e procuram-se no catálogo); sem ele, carrega-se aqui.
     */
    public function prefillFor(?Client $client, ?FormProposal $form = null, ?ImportOpportunity $opportunity = null, ?Collection $brands = null): array
    {
        if ($opportunity) {
            $formProposal = $opportunity->formProposal;
            [$catalogBrand, $catalogModel] = $this->catalogMatch($opportunity, $brands ?? $this->catalog());

            return [
                'client_id'               => $client?->id ?? $formProposal?->client_id,
                'brand'                   => $catalogBrand?->name ?? $opportunity->brand,
                'model'                   => $catalogModel?->name ?? $opportunity->model,
                'version'                 => $opportunity->version,
                'fuel'                    => $opportunity->fuel?->proposalLabel(),
                'url'                     => $opportunity->listing_url,
                'proposed_car_year_month' => $opportunity->year,
                'proposed_car_mileage'    => $opportunity->mileage,
                'proposed_car_value'      => $opportunity->price,
                // Notas internas (não aparecem na página pública da cotação).
                'notes'                   => implode("\n\n", array_filter([$opportunity->vehicle_notes, $formProposal?->message])) ?: null,
            ];
        }

        if ($form) {
            return [
                'client_id'           => $client?->id ?? $form->client_id,
                'brand'               => $form->brand,
                'model'               => $form->model,
                'version'             => $form->version,
                'fuel'                => $form->fuel,
                'proposed_car_value'  => $form->budget,
                'proposed_car_mileage'=> $form->km_max,
                'notes'               => $form->message,
            ];
        }

        return ['client_id' => $client?->id];
    }

    /**
     * Marca/modelo do catálogo equivalentes aos da oportunidade (sem
     * diferenciar maiúsculas), ou null.
     *
     * @return array{0: ?Brand, 1: ?\App\Models\ModelCar}
     */
    public function catalogMatch(ImportOpportunity $opportunity, Collection $brands): array
    {
        $sameName = fn (string $a, ?string $b) => mb_strtolower(trim($a)) === mb_strtolower(trim((string) $b));
        $catalogBrand = $brands->first(fn ($brand) => $sameName($brand->name, $opportunity->brand));
        $catalogModel = $catalogBrand?->models->first(fn ($model) => $sameName($model->name, $opportunity->model));

        return [$catalogBrand, $catalogModel];
    }

    /** Catálogo de marcas com modelos, como nos selects do formulário. */
    public function catalog(): Collection
    {
        return Brand::with(['models' => function ($q) { $q->orderBy('name'); }])->get();
    }

    /** Uma cotação por oportunidade: a que já existe, se existir. */
    public function existingForOpportunity(?ImportOpportunity $opportunity): ?Proposal
    {
        return $opportunity?->proposal_id ? $opportunity->proposal : null;
    }

    /**
     * Pedido e oportunidade a ligar a uma cotação de $client, confirmando que
     * são desse cliente (a oportunidade traz o seu pedido).
     *
     * @return array{0: ?FormProposal, 1: ?ImportOpportunity}
     *
     * @throws ValidationException
     */
    public function linksFor(Client $client, ?int $formProposalId, ?int $opportunityId): array
    {
        $opportunity = $opportunityId ? ImportOpportunity::with('formProposal')->find($opportunityId) : null;
        $form = $formProposalId ? FormProposal::find($formProposalId) : $opportunity?->formProposal;

        if ($opportunityId && (! $opportunity || (int) $opportunity->formProposal?->client_id !== (int) $client->id)) {
            throw ValidationException::withMessages(['import_opportunity_id' => 'A oportunidade não pertence a esta lead/cliente.']);
        }

        if ($formProposalId && (! $form || (int) $form->client_id !== (int) $client->id)) {
            throw ValidationException::withMessages(['form_proposal_id' => 'O pedido não pertence a esta lead/cliente.']);
        }

        if ($opportunity && $form && (int) $opportunity->form_proposal_id !== (int) $form->id) {
            throw ValidationException::withMessages(['import_opportunity_id' => 'A oportunidade não pertence a este pedido.']);
        }

        return [$form, $opportunity];
    }

    /**
     * Nova cotação. $data já validado com rules() (sem image e sem as
     * ligações). Nasce sempre "Pendente": o estado pedido aplica-se no fim,
     * pelo serviço de aceitação (ex. "Aprovada" cria a cotação convertida).
     * $attributes: atributos personalizados (extras, características).
     *
     * @throws ProposalStatusNotAppliedException a cotação ficou criada, mas o estado não foi aplicado
     */
    public function create(array $data, ?UploadedFile $image, ?int $formProposalId, ?int $opportunityId, ?array $attributes = null, ?User $by = null): Proposal
    {
        unset($data['image'], $data['form_proposal_id'], $data['import_opportunity_id']);
        $importOpportunity = $opportunityId ? ImportOpportunity::find($opportunityId) : null;

        // Gerar código único para a proposta
        // (mb_*: uma inicial acentuada, ex. "Élan", não pode ficar cortada a meio do byte)
        $data['proposal_code'] = mb_strtoupper(mb_substr($data['brand'], 0, 1) .
            mb_substr($data['model'], 0, 1) .
            Str::random(8));

        // Converter array de links para JSON
        if (isset($data['other_links'])) {
            $data['other_links'] = json_encode($data['other_links']);
        }

        $requestedStatus = $data['status'] ?? 'Pendente';
        $data['status'] = 'Pendente';

        $proposal = Proposal::create($data);

        $this->saveAttributes($proposal, $attributes ?? []);

        // Imagem enviada; sem upload, usa a foto da oportunidade
        if ($image) {
            $this->storeImage($proposal, $image);
        } elseif ($importOpportunity && ($photo = $this->opportunities->photoAsUpload($importOpportunity))) {
            $this->storeImage($proposal, $photo);
        }

        // Ligar ao pedido de origem. Vindo de uma oportunidade, o pedido pode
        // ter várias cotações — mantém a primeira. O pedido fica "em análise";
        // só passa a "convertido" quando uma cotação for aceite.
        if ($formProposalId) {
            $formProposal = FormProposal::find($formProposalId);
            if ($formProposal) {
                $formProposal->update([
                    'proposal_id' => $importOpportunity && $formProposal->proposal_id ? $formProposal->proposal_id : $proposal->id,
                    'status'      => in_array($formProposal->status, [null, 'novo'], true) ? 'em_analise' : $formProposal->status,
                ]);
            }
        }

        if ($importOpportunity) {
            $this->opportunities->linkProposal($importOpportunity, $proposal, $by);
        }

        // Registar na timeline do cliente/lead
        $vehicle = implode(' ', array_filter([$proposal->brand, $proposal->model, $proposal->version]));
        LeadActivity::log(
            $proposal->client_id,
            "Cotação #{$proposal->id} criada" . ($vehicle ? " — {$vehicle}" : ''),
            $proposal->total_price ? "Valor total: " . number_format($proposal->total_price, 0, ',', '.') . " €." : '',
            'bi-file-earmark-text-fill',
            'primary'
        );

        if ($requestedStatus !== 'Pendente') {
            try {
                $this->acceptance->changeStatus($proposal, $requestedStatus);
            } catch (ProposalAcceptanceException $e) {
                throw new ProposalStatusNotAppliedException($proposal, $e);
            }
        }

        return $proposal;
    }

    /**
     * Edita a cotação. $data já validado com updateRules(). Uma cotação aceite
     * não muda de estado (recusa antes de gravar). $attributes null mantém os
     * atributos; um array (mesmo vazio) recria-os todos, como no desktop.
     *
     * @throws ProposalAcceptanceException cotação aceite com outro estado pedido (nada foi gravado)
     * @throws ProposalStatusNotAppliedException gravada, mas o estado não foi aplicado
     */
    public function update(Proposal $proposal, array $data, ?UploadedFile $image, ?array $attributes = null): Proposal
    {
        unset($data['image']);

        // Converter array de links para JSON
        if (isset($data['other_links'])) {
            $data['other_links'] = json_encode($data['other_links']);
        }

        $oldStatus = $proposal->status;
        $requestedStatus = $data['status'] ?? $oldStatus;
        unset($data['status']);

        if ($proposal->isAccepted() && $requestedStatus !== 'Aprovada') {
            throw new ProposalAcceptanceException('Esta cotação já foi aceite e não pode mudar de estado — para a anular, cancele a cotação convertida.');
        }

        // Atualizar proposta (o estado é aplicado no fim, pelo serviço)
        $proposal->update($data);

        if ($attributes !== null) {
            $proposal->attributeValues()->delete();
            $this->saveAttributes($proposal, $attributes);
        }

        // Nova imagem (se existir, substitui a antiga)
        if ($image) {
            $this->storeImage($proposal, $image);
        }

        if ($requestedStatus && $requestedStatus !== $oldStatus) {
            try {
                $this->acceptance->changeStatus($proposal->fresh(), $requestedStatus);
            } catch (ProposalAcceptanceException $e) {
                throw new ProposalStatusNotAppliedException($proposal, $e);
            }
        }

        // Registar mudança de estado na timeline (só se mudou)
        $newStatus = $proposal->fresh()->status;
        if ($oldStatus !== $newStatus) {
            LeadActivity::log(
                $proposal->client_id,
                "Cotação #{$proposal->id}: estado alterado para {$newStatus}",
                '',
                'bi-arrow-repeat',
                self::STATUS_COLORS[$newStatus] ?? 'secondary'
            );
        }

        return $proposal;
    }

    /**
     * Mudança rápida de estado. Devolve se o cliente foi avisado por email
     * (passou a "Enviado", com aviso, e o cliente tem email).
     *
     * @throws ProposalAcceptanceException
     */
    public function changeStatus(Proposal $proposal, string $status, bool $notify = true): bool
    {
        $changed = $proposal->status !== $status;

        $this->acceptance->changeStatus($proposal, $status, $notify);

        return $changed && $notify && $status === 'Enviado' && filled($proposal->client?->email);
    }

    /**
     * Aceitação no backoffice: pode aceitar-se mesmo expirada/reprovada (ex.
     * cliente que aceitou por telefone); não envia emails nem contrato.
     *
     * @throws ProposalAcceptanceException
     */
    public function accept(Proposal $proposal): ConvertedProposal
    {
        return $this->acceptance->accept($proposal, [], false);
    }

    /** Valores dos atributos personalizados; ignora os vazios. */
    private function saveAttributes(Proposal $proposal, array $attributes): void
    {
        foreach ($attributes as $attributeId => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            ProposalAttributeValue::create([
                'proposal_id' => $proposal->id,
                'attribute_id' => $attributeId,
                'value' => is_array($value) ? json_encode($value) : $value,
            ]);
        }
    }

    /**
     * Guarda a imagem da cotação (AVIF + WebP em várias medidas) e apaga a
     * anterior, com todas as variantes.
     */
    private function storeImage(Proposal $proposal, UploadedFile $image): void
    {
        if ($proposal->images) {
            $existing = is_array($proposal->images) ? $proposal->images[0] : $proposal->images;
            (new ImageOptimizerService())->deleteAllVariants($existing);
        }

        $path = (new ImageOptimizerService())->optimize(
            $image,
            "proposals/{$proposal->client_id}/{$proposal->id}"
        );

        // Caminho canónico (large AVIF).
        $proposal->images = $path;
        $proposal->save();
    }
}
