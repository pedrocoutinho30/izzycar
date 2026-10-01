<?php

namespace App\Services;

use App\Models\ConvertedProposal;
use App\Models\ImportOpportunity;
use App\Models\LeadActivity;
use App\Models\Legalization;
use App\Models\V3Vehicle;
use Illuminate\Support\Facades\DB;

/**
 * Passos seguintes de uma cotação convertida (importação aceite): a viatura
 * do cliente e a legalização nascem com os dados que a cotação já tem, em
 * vez de voltarem a ser escritos à mão.
 */
class ConvertedProposalService
{
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
