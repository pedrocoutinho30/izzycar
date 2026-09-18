<?php

namespace App\Services;

use App\Models\RadarSearch;
use Illuminate\Support\Collection;

/**
 * Calcula um ranking de "melhor combinação ano + kms + preço" dentro de cada
 * origem de uma pesquisa (AutoScout24 e Standvirtual, separadamente), e
 * assinala anúncios da AutoScout24 (Alemanha) que batem objetivamente o
 * melhor anúncio de Portugal nessa mesma combinação.
 *
 * O "score" de cada anúncio soma 3 frações (0 a 1 cada, quanto maior melhor):
 * preço mais baixo, quilometragem mais baixa, ano mais recente - cada uma
 * normalizada pelo min/max de TODOS os anúncios ativos da pesquisa (as duas
 * origens juntas), para que o score fique diretamente comparável entre
 * mercados, não só dentro de cada um - é isso que permite comparar um
 * anúncio alemão com o melhor anúncio português.
 *
 * O preço usado para os anúncios da AutoScout24 (Alemanha/Europa) inclui
 * sempre o "custo de importação" definido na pesquisa (radar_searches.
 * import_cost_eur) - a comparação/ranking deve refletir o custo REAL de trazer
 * o carro para Portugal, não só o preço de tabela no anúncio original.
 *
 * O preço usado no score é também dividido pelo trim_price_multiplier do
 * anúncio (classificado por IA - ver RadarTrimPriceAiService/
 * ClassifyRadarListingTrims), para que versões topo de gama (ex.: "Turbo S")
 * não pareçam sempre má compra só por serem naturalmente mais caras do que a
 * versão base (ex.: "4S") do mesmo modelo.
 */
class RadarValueScoreService
{
    public function analyze(RadarSearch $radarSearch): array
    {
        $de = $this->fetchRows($radarSearch, 'autoscout24', priceOffset: (float) ($radarSearch->import_cost_eur ?? 0));
        $pt = $this->fetchRows($radarSearch, ['standvirtual', 'carmine'], onlyIncludedInAverage: true);

        $bounds = $this->bounds($de->concat($pt));

        $deScored = $this->scoreRows($de, $bounds);
        $ptScored = $this->scoreRows($pt, $bounds);

        $bestPtScore = $ptScored->max('score');

        $deStars = [];
        if ($bestPtScore !== null) {
            foreach ($deScored as $row) {
                if ($row['score'] > $bestPtScore) {
                    $deStars[$row['id']] = true;
                }
            }
        }

        return [
            'de_ranks' => $this->ranks($deScored),
            'pt_ranks' => $this->ranks($ptScored),
            'de_stars' => $deStars,
            'de_scores' => $deScored->pluck('score', 'id')->all(),
            'pt_scores' => $ptScored->pluck('score', 'id')->all(),
        ];
    }

    /**
     * Traduz o score bruto (0 a 3 - soma de 3 frações 0/1, 1.5 = neutro) num
     * nível de oportunidade para apresentação visual (cor discreta + rótulo),
     * usado nos cartões do Radar em vez do número em bruto.
     *
     * @return array{label: string, class: string}
     */
    public static function tier(?float $score): array
    {
        if ($score === null) {
            return ['label' => 'Sem dados', 'class' => 'radar-tier-none'];
        }

        return match (true) {
            $score >= 2.0 => ['label' => 'Excelente', 'class' => 'radar-tier-excelente'],
            $score >= 1.5 => ['label' => 'Bom', 'class' => 'radar-tier-bom'],
            $score >= 1.0 => ['label' => 'Médio', 'class' => 'radar-tier-medio'],
            default => ['label' => 'Fraco', 'class' => 'radar-tier-fraco'],
        };
    }

    /** @param string|array<int, string> $source */
    private function fetchRows(RadarSearch $radarSearch, $source, bool $onlyIncludedInAverage = false, float $priceOffset = 0): Collection
    {
        $query = $radarSearch->listings()
            ->where(fn ($q) => is_array($source) ? $q->whereIn('source', $source) : $q->where('source', $source))
            ->whereNull('removed_at')
            ->whereNull('duplicate_of_listing_id')
            ->whereNotNull('price_eur')
            ->whereNotNull('mileage_km')
            ->whereNotNull('first_registration_year');

        if ($onlyIncludedInAverage) {
            $query->where('include_in_average', true);
        }

        // Mesma exigência "tem de ter TODO o equipamento selecionado" que
        // RadarController::applyEquipmentFilter() aplica às tabelas - o ranking deve
        // refletir só os anúncios que passam o filtro, não a pesquisa inteira.
        foreach ($radarSearch->equipment()->pluck('radar_equipment.id') as $equipmentId) {
            $query->whereHas('equipment', fn ($q) => $q->where('radar_equipment.id', $equipmentId));
        }

        $rows = $query->get(['id', 'price_eur', 'mileage_km', 'first_registration_year', 'trim_price_multiplier']);

        if ($priceOffset != 0) {
            $rows->each(fn ($row) => $row->price_eur += $priceOffset);
        }

        // Preço ajustado ao nível de trim/versão (ver RadarTrimPriceAiService):
        // uma versão topo de gama naturalmente mais cara não deve parecer "má
        // compra" só por isso - o score deve refletir se é um bom negócio PARA
        // essa versão, não o preço absoluto. Sem classificação (ainda não
        // processado pela IA), o multiplicador é 1.0 e nada muda.
        $rows->each(function ($row) {
            $row->adjusted_price = $row->price_eur / ($row->trim_price_multiplier ?: 1.0);
        });

        return $rows;
    }

    private function bounds(Collection $rows): array
    {
        return [
            'price' => [$rows->min('adjusted_price'), $rows->max('adjusted_price')],
            'km' => [$rows->min('mileage_km'), $rows->max('mileage_km')],
            'year' => [$rows->min('first_registration_year'), $rows->max('first_registration_year')],
        ];
    }

    private function fraction($value, $min, $max, bool $higherIsBetter): float
    {
        if ($max === null || $min === null || $max == $min) {
            return 0.5; // sem variação nesta dimensão (ou sem dados) - não penaliza nem beneficia ninguém
        }

        $fraction = ($value - $min) / ($max - $min);

        return $higherIsBetter ? $fraction : 1 - $fraction;
    }

    private function scoreRows(Collection $rows, array $bounds): Collection
    {
        return $rows->map(function ($row) use ($bounds) {
            $score = $this->fraction($row->adjusted_price, $bounds['price'][0], $bounds['price'][1], false)
                + $this->fraction($row->mileage_km, $bounds['km'][0], $bounds['km'][1], false)
                + $this->fraction($row->first_registration_year, $bounds['year'][0], $bounds['year'][1], true);

            return ['id' => $row->id, 'score' => $score];
        });
    }

    /** @return array<int, int> id do anúncio => posição no ranking (1 = melhor combinação) */
    private function ranks(Collection $scored): array
    {
        return $scored->sortByDesc('score')->values()
            ->mapWithKeys(fn ($row, $index) => [$row['id'] => $index + 1])
            ->all();
    }
}
