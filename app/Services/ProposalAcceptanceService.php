<?php

namespace App\Services;

use App\Models\Client;
use App\Enums\OpportunityStatus;
use App\Models\ConvertedProposal;
use App\Models\FormProposal;
use App\Models\ImportOpportunity;
use App\Models\LeadActivity;
use App\Models\Proposal;
use App\Models\StatusProposalHistory;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Aceitação de uma cotação → cotação convertida. Usado pela página pública
 * (o cliente aceita) e pelo backoffice. Tudo é verificado ANTES de gravar e
 * corre numa transação com a cotação bloqueada, para que um duplo clique ou
 * dois separadores nunca criem duas cotações convertidas.
 */
class ProposalAcceptanceService
{
    /** Campos do cliente que a página pública pode completar. */
    public const CLIENT_FIELDS = [
        'email', 'phone', 'address', 'postal_code', 'city',
        'vat_number', 'identification_number', 'validate_identification_number',
    ];

    /**
     * @param  array<string, mixed>  $clientData  dados enviados pelo cliente — só preenchem campos vazios
     * @param  bool  $byClient  aceitação feita pelo próprio cliente (página pública): recusa expiradas e reprovadas
     *
     * @throws ProposalAcceptanceException
     */
    public function accept(Proposal $proposal, array $clientData = [], bool $byClient = true): ConvertedProposal
    {
        try {
            return DB::transaction(function () use ($proposal, $clientData, $byClient) {
                $proposal = Proposal::whereKey($proposal->getKey())->lockForUpdate()->firstOrFail();

                $this->ensureAcceptable($proposal, $byClient);

                $client = Client::whereKey($proposal->client_id)->lockForUpdate()->first();
                if (!$client) {
                    throw new ProposalAcceptanceException('Esta cotação não tem cliente associado.');
                }

                $this->completeClient($client, $clientData);

                if ($client->is_lead) {
                    $client->convertToClient();
                    LeadActivity::log($client->id, 'Lead convertido em cliente', "Convertido automaticamente ao aceitar a cotação #{$proposal->id}.", 'bi-person-check-fill', 'success');
                }

                $proposal->status = 'Aprovada';
                $proposal->save();

                $converted = ConvertedProposal::create($this->convertedData($proposal, $client));

                StatusProposalHistory::create([
                    'new_status' => 'Iniciada',
                    'old_status' => null,
                    'converted_proposal_id' => $converted->id,
                ]);

                // O pedido de importação só fica "convertido" quando há cotação aceite.
                $this->linkedRequests($proposal)->each(fn (FormProposal $request) => $request->update(['status' => 'convertido']));

                LeadActivity::log(
                    $client->id,
                    'Cotação aceite',
                    ($byClient ? 'Aceite pelo cliente na página da cotação' : 'Aceite no backoffice') . " — cotação #{$proposal->id}, cotação convertida #{$converted->id}.",
                    'bi-check-circle-fill',
                    'success'
                );

                return $converted;
            });
        } catch (UniqueConstraintViolationException) {
            // Outro pedido aceitou-a entretanto (índice único em proposal_id).
            throw new ProposalAcceptanceException('Esta cotação já foi aceite.');
        }
    }

    /**
     * Única forma de mudar o estado de uma cotação no backoffice:
     * - "Aprovada" passa sempre pela aceitação (cria a cotação convertida);
     * - uma cotação aceite não volta a outro estado (cancela-se a convertida);
     * - "Reprovada" marca como rejeitada a oportunidade que lhe deu origem.
     * Grava pelo model, para o observer correr (emails "Enviado", log).
     *
     * @throws ProposalAcceptanceException
     */
    public function changeStatus(Proposal $proposal, string $status): void
    {
        if ($proposal->status === $status) {
            return;
        }

        if ($proposal->isAccepted() && $status !== 'Aprovada') {
            throw new ProposalAcceptanceException("A cotação #{$proposal->id} já foi aceite e não pode mudar de estado — para a anular, cancele a cotação convertida.");
        }

        if ($status === 'Aprovada') {
            $this->accept($proposal, byClient: false);

            return;
        }

        $proposal->status = $status;
        $proposal->save();

        if ($status === 'Reprovada') {
            ImportOpportunity::where('proposal_id', $proposal->id)
                ->where('status', '!=', OpportunityStatus::Rejected->value)
                ->get()
                ->each(fn (ImportOpportunity $opportunity) => $opportunity->update(['status' => OpportunityStatus::Rejected]));
        }
    }

    /**
     * Pedidos de importação a que a cotação pertence: o pedido aponta para
     * ela, ou a cotação nasceu de uma oportunidade do pedido.
     *
     * @return \Illuminate\Support\Collection<int, FormProposal>
     */
    public function linkedRequests(Proposal $proposal): \Illuminate\Support\Collection
    {
        $ids = FormProposal::where('proposal_id', $proposal->id)->pluck('id')
            ->merge(ImportOpportunity::where('proposal_id', $proposal->id)->pluck('form_proposal_id'))
            ->unique();

        return FormProposal::whereIn('id', $ids)->get();
    }

    /** @throws ProposalAcceptanceException */
    public function ensureAcceptable(Proposal $proposal, bool $byClient = true): void
    {
        if ($proposal->isAccepted()) {
            throw new ProposalAcceptanceException('Esta cotação já foi aceite.');
        }

        if ($byClient && $proposal->isExpired()) {
            throw new ProposalAcceptanceException('Esta cotação já expirou. Contacte-nos para receber uma cotação atualizada.');
        }

        if ($byClient && $proposal->isRejected()) {
            throw new ProposalAcceptanceException('Esta cotação já não está disponível. Contacte-nos para receber uma cotação atualizada.');
        }
    }

    /**
     * Só completa o que falta: dados que o cliente já tem nunca são
     * substituídos a partir de um formulário público.
     */
    private function completeClient(Client $client, array $data): void
    {
        $fill = collect($data)
            ->only(self::CLIENT_FIELDS)
            ->filter(fn ($value) => filled($value))
            ->filter(fn ($value, $field) => blank($client->getAttribute($field)))
            ->all();

        if ($fill) {
            $client->update($fill);
        }
    }

    private function convertedData(Proposal $proposal, Client $client): array
    {
        // Tranches: serviço Izzycar (custos do processo + comissão), 50/50.
        // O ISV e o valor do carro não passam pela Izzycar.
        $serviceTotal = $proposal->transport_cost
            + $proposal->inspection_commission_cost
            + $proposal->ipo_cost
            + $proposal->imt_cost
            + $proposal->registration_cost
            + $proposal->license_plate_cost
            + $proposal->commission_cost;

        return [
            'proposal_id' => $proposal->id,
            'client_id' => $proposal->client_id,
            // Angariador desta cotação convertida — copiado do cliente agora,
            // mas independente dele daqui em diante.
            'owner_id' => $client->owner_id,
            'status' => 'Iniciada',
            'brand' => $proposal->brand,
            'modelCar' => $proposal->model,
            'version' => $proposal->version,
            'year' => $proposal->proposed_car_year_month,
            'km' => $proposal->proposed_car_mileage,
            'url' => $proposal->url,
            'custo_inspecao_origem' => $proposal->inspection_commission_cost,
            'custo_transporte' => $proposal->transport_cost,
            'custo_ipo' => $proposal->ipo_cost,
            'isv' => $proposal->isv_cost,
            'custo_imt' => $proposal->imt_cost,
            'custo_matricula' => $proposal->license_plate_cost,
            'custo_registo_automovel' => $proposal->registration_cost,
            'valor_primeira_tranche' => $serviceTotal * 0.5,
            'valor_segunda_tranche' => $serviceTotal * 0.5,
            'valor_carro' => $proposal->proposed_car_value,
            'valor_comissao' => $proposal->commission_cost,
        ];
    }
}
