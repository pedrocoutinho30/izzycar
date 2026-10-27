<?php

namespace App\Services;

use App\Mail\ProposalStatusUpdatedMail;
use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\ImportOpportunity;
use App\Models\LeadActivity;
use App\Models\Legalization;
use App\Models\User;
use App\Models\V3Vehicle;
use App\Permissions\PermissionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

/**
 * Passos seguintes de uma cotação convertida (importação aceite): a viatura
 * do cliente e a legalização nascem com os dados que a cotação já tem, em
 * vez de voltarem a ser escritos à mão.
 */
class ConvertedProposalService
{
    /** Estados aceites na mudança de estado: as etapas do pipeline e as duas grafias de cancelada. */
    public const STATUSES = [...ConvertedProposal::PIPELINE_STATUSES, ...ConvertedProposal::CANCELLED_STATUSES];

    public function __construct(private ?PermissionService $permissions = null)
    {
    }

    /** Validação da mudança de estado (a mesma no desktop e no mobile). */
    public function statusRules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(self::STATUSES)],
            // Sem o campo mantém-se o comportamento antigo: avisar o cliente.
            'notify' => ['nullable', 'boolean'],
        ];
    }

    public function statusMessages(): array
    {
        return ['status.in' => 'Estado inválido.'];
    }

    /**
     * Muda o estado (o hook do modelo grava o StatusProposalHistory) e, se
     * $notify, avisa o cliente por email. Sem alteração não grava nem envia
     * nada. Um cliente sem email ou uma falha no envio não impedem a mudança.
     *
     * @return array{changed: bool, notified: bool, oldStatus: ?string, newStatus: string}
     */
    public function changeStatus(ConvertedProposal $convertedProposal, string $status, bool $notify = true): array
    {
        if (!in_array($status, self::STATUSES, true)) {
            throw new \InvalidArgumentException('Estado inválido.');
        }

        $oldStatus = $convertedProposal->status;

        if ($oldStatus === $status) {
            return ['changed' => false, 'notified' => false, 'oldStatus' => $oldStatus, 'newStatus' => $status];
        }

        $convertedProposal->status = $status;
        $convertedProposal->save();

        $notified = false;
        $client = Client::find($convertedProposal->client_id);
        if ($notify && $client && filled($client->email)) {
            try {
                Mail::to($client->email)->bcc('izzycarpt@gmail.com')->send(
                    new ProposalStatusUpdatedMail($convertedProposal, $oldStatus, $status, $client->name, $convertedProposal->matricula_destino)
                );
                $notified = true;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return ['changed' => true, 'notified' => $notified, 'oldStatus' => $oldStatus, 'newStatus' => $status];
    }

    /**
     * Âmbito de consulta no mobile: "all" com a permissão de ver cotações
     * convertidas; "own" para quem vê cotações (as convertidas dos clientes de
     * que é dono, como a área do angariador no desktop); null sem acesso.
     */
    public function viewScope(User $user): ?string
    {
        $permissions = $this->permissions();

        if ($permissions->allows($user, 'converted-proposals', 'view')) {
            return 'all';
        }

        return $permissions->scope($user, 'proposals', 'view') !== null ? 'own' : null;
    }

    /** Restringe a query às convertidas que o utilizador pode consultar. */
    public function applyViewScope(Builder $query, User $user): Builder
    {
        return match ($this->viewScope($user)) {
            'all' => $query,
            'own' => $query->whereHas('client', fn (Builder $q) => $q->where('owner_id', $user->getKey())),
            default => $query->whereRaw('1 = 0'),
        };
    }

    public function canView(User $user, ConvertedProposal $convertedProposal): bool
    {
        return match ($this->viewScope($user)) {
            'all' => true,
            'own' => $convertedProposal->client !== null && (int) $convertedProposal->client->owner_id === (int) $user->getKey(),
            default => false,
        };
    }

    /** Mudar o estado exige editar cotações convertidas, como a rota update-status do desktop. */
    public function canChangeStatus(User $user): bool
    {
        return $this->permissions()->allows($user, 'converted-proposals', 'update');
    }

    private function permissions(): PermissionService
    {
        return $this->permissions ??= app(PermissionService::class);
    }
    /**
     * Cria (ou reaproveita) a viatura e a legalização desta cotação convertida.
     * Pode ser chamado várias vezes sem duplicar nada.
     *
     * @return array{vehicle: V3Vehicle, legalization: Legalization, created: bool}
     */
    public function createVehicleAndLegalization(ConvertedProposal $converted): array
    {
        return DB::transaction(function () use ($converted) {
            $converted = ConvertedProposal::whereKey($converted->getKey())->lockForUpdate()->firstOrFail();
            $created = false;

            $vehicle = $converted->v3Vehicle;
            if (!$vehicle) {
                $vehicle = V3Vehicle::create($this->vehicleData($converted));
                $converted->update(['v3_vehicle_id' => $vehicle->id]);
                $created = true;
            }

            $legalization = Legalization::firstOrCreate(
                ['v3_vehicle_id' => $vehicle->id],
                [
                    'client_id' => $converted->client_id,
                    'marca' => $vehicle->brand ?? '',
                    'modelo' => trim(($vehicle->model ?? '') . ' ' . ($vehicle->sub_model ?? '')),
                    'combustivel' => $vehicle->fuel ?? 'Gasolina',
                    'matricula' => $vehicle->registration,
                    'steps_completed' => [],
                ]
            );

            if ($created && $converted->client_id) {
                LeadActivity::log(
                    $converted->client_id,
                    'Viatura e legalização criadas',
                    "A partir da cotação convertida #{$converted->id}: viatura {$vehicle->reference}.",
                    'bi-car-front-fill',
                    'primary'
                );
            }

            return ['vehicle' => $vehicle, 'legalization' => $legalization, 'created' => $created];
        });
    }

    private function vehicleData(ConvertedProposal $converted): array
    {
        $proposal = $converted->proposal;
        // Vendedor da oportunidade que deu origem à cotação (se houver).
        $opportunity = $proposal ? ImportOpportunity::where('proposal_id', $proposal->id)->first() : null;
        preg_match('/(19|20)\d{2}/', (string) $converted->year, $year);

        return [
            'reference' => V3Vehicle::generateReference(),
            'brand' => $converted->brand,
            'model' => $converted->modelCar,
            'version' => $converted->version,
            'year' => isset($year[0]) ? (int) $year[0] : null,
            'kilometers' => $converted->km,
            'fuel' => $this->fuel($proposal?->fuel),
            'registration' => $converted->matricula_destino ?: null,
            // Importação: o carro é do cliente — não é stock à venda.
            'is_imported' => true,
            'client_id' => $converted->client_id,
            'status' => 'reservado',
            'show_online' => false,
            'seller_id' => $opportunity?->seller_id,
            'seller_contact_id' => $opportunity?->seller_contact_id,
            'notes' => "Importação — cotação convertida #{$converted->id}"
                . ($converted->matricula_origem ? ". Matrícula de origem: {$converted->matricula_origem}" : '')
                . ($converted->url ? ". Anúncio: {$converted->url}" : ''),
        ];
    }

    /** Combustível da cotação → opções da viatura (V3Vehicle::fuelOptions). */
    private function fuel(?string $fuel): ?string
    {
        if (blank($fuel)) {
            return null;
        }

        return str_starts_with($fuel, 'Híbrido Plug-in') ? 'Híbrido Plug-In' : $fuel;
    }
}
