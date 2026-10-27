<?php

namespace App\Services;

use App\Models\Client;
use App\Models\FormProposal;
use App\Models\Legalization;
use App\Models\Proposal;
use App\Models\Sale;
use App\Models\V3Vehicle;
use Illuminate\Database\Eloquent\Builder;

/**
 * Consultas da pesquisa global — procura por aproximação (LIKE) nos
 * principais campos de cada tipo de registo. Partilhada pela pesquisa do
 * desktop (GlobalSearchController) e pela do mobile (Mobile\SearchController):
 * cada uma decide as secções, o limite, o âmbito e para onde ligam os resultados.
 *
 * Com $literal = true, os curingas do LIKE (% e _) escritos pelo utilizador
 * contam como texto (o desktop mantém o comportamento antigo: false).
 */
class GlobalSearchService
{
    /** Termo mínimo, igual no desktop e no mobile. */
    public const MIN_LENGTH = 2;

    /** Carácter de escape portátil (MySQL e sqlite) para o LIKE literal. */
    private const ESCAPE = '!';

    public function leads(string $term, bool $literal = false): Builder
    {
        return Client::where('is_lead', true)
            ->where(fn (Builder $q) => $this->contactMatch($q, $term, $literal));
    }

    public function clients(string $term, bool $literal = false): Builder
    {
        return Client::where('is_lead', false)
            ->where(fn (Builder $q) => $this->contactMatch($q, $term, $literal));
    }

    public function proposals(string $term, bool $literal = false): Builder
    {
        return Proposal::where(function (Builder $q) use ($term, $literal) {
            $this->like($q, 'brand', $term, $literal);
            $this->like($q, 'model', $term, $literal, 'or');
            $this->like($q, 'version', $term, $literal, 'or');
            $this->like($q, 'proposal_code', $term, $literal, 'or');
            $q->orWhereHas('client', fn ($q2) => $this->like($q2, 'name', $term, $literal));
            $this->applyFieldComboMatch($q, $term, 'brand', 'model', $literal);
        })->with('client');
    }

    public function formProposals(string $term, bool $literal = false): Builder
    {
        return FormProposal::where(function (Builder $q) use ($term, $literal) {
            $this->like($q, 'name', $term, $literal);
            $this->like($q, 'email', $term, $literal, 'or');
            $this->like($q, 'phone', $term, $literal, 'or');
            $this->like($q, 'brand', $term, $literal, 'or');
            $this->like($q, 'model', $term, $literal, 'or');
            $this->applyFieldComboMatch($q, $term, 'brand', 'model', $literal);
        });
    }

    public function legalizations(string $term, bool $literal = false): Builder
    {
        return Legalization::where(function (Builder $q) use ($term, $literal) {
            $this->like($q, 'marca', $term, $literal);
            $this->like($q, 'modelo', $term, $literal, 'or');
            $this->like($q, 'matricula', $term, $literal, 'or');
            $this->like($q, 'num_processo_imt', $term, $literal, 'or');
            $q->orWhereHas('client', fn ($q2) => $this->like($q2, 'name', $term, $literal));
            $this->applyFieldComboMatch($q, $term, 'marca', 'modelo', $literal);
        })->with('client');
    }

    public function vehicles(string $term, bool $literal = false): Builder
    {
        return V3Vehicle::where(function (Builder $q) use ($term, $literal) {
            $this->like($q, 'brand', $term, $literal);
            $this->like($q, 'model', $term, $literal, 'or');
            $this->like($q, 'reference', $term, $literal, 'or');
            $this->like($q, 'vin', $term, $literal, 'or');
            $this->like($q, 'registration', $term, $literal, 'or');
            $this->applyFieldComboMatch($q, $term, 'brand', 'model', $literal);
        });
    }

    public function sales(string $term, bool $literal = false): Builder
    {
        $vehicleMatch = function (Builder $q2) use ($term, $literal) {
            $this->like($q2, 'brand', $term, $literal);
            $this->like($q2, 'model', $term, $literal, 'or');
            $this->applyFieldComboMatch($q2, $term, 'brand', 'model', $literal);
        };

        return Sale::where(function (Builder $q) use ($term, $literal, $vehicleMatch) {
            $q->whereHas('client', fn ($q2) => $this->like($q2, 'name', $term, $literal))
                ->orWhereHas('vehicle', $vehicleMatch)
                ->orWhereHas('v3Vehicle', $vehicleMatch);
        })->with(['client', 'vehicle', 'v3Vehicle']);
    }

    /** Nome, email, telefone, NIF ou documento de identificação (leads e clientes). */
    private function contactMatch(Builder $q, string $term, bool $literal): void
    {
        $this->like($q, 'name', $term, $literal);
        foreach (['email', 'phone', 'vat_number', 'identification_number'] as $column) {
            $this->like($q, $column, $term, $literal, 'or');
        }
    }

    /**
     * Quando o termo tem várias palavras (ex: "Mercedes CLA"), tenta também
     * interpretá-las como dois campos separados — a primeira palavra num
     * campo e o resto no outro, nas duas ordens possíveis. Isto permite
     * encontrar "Mercedes CLA" mesmo quando "Mercedes" está no campo marca
     * e "CLA" está no campo modelo (nenhum dos dois campos, isolado, contém
     * a frase completa).
     */
    private function applyFieldComboMatch(Builder $query, string $term, string $field1, string $field2, bool $literal): void
    {
        $words = preg_split('/\s+/', trim($term), -1, PREG_SPLIT_NO_EMPTY);

        if (count($words) < 2) {
            return;
        }

        $first = $words[0];
        $rest = implode(' ', array_slice($words, 1));

        $query->orWhere(function (Builder $q) use ($field1, $field2, $first, $rest, $literal) {
            $this->like($q, $field1, $first, $literal);
            $this->like($q, $field2, $rest, $literal);
        })->orWhere(function (Builder $q) use ($field1, $field2, $first, $rest, $literal) {
            $this->like($q, $field1, $rest, $literal);
            $this->like($q, $field2, $first, $literal);
        });
    }

    /** "coluna LIKE %termo%"; em modo literal escapa % e _ (e o próprio carácter de escape). */
    private function like(Builder $q, string $column, string $term, bool $literal, string $boolean = 'and'): void
    {
        if (!$literal) {
            $q->where($column, 'like', "%{$term}%", $boolean);

            return;
        }

        $escaped = str_replace([self::ESCAPE, '%', '_'], [self::ESCAPE . self::ESCAPE, self::ESCAPE . '%', self::ESCAPE . '_'], $term);
        $wrapped = $q->getQuery()->getGrammar()->wrap($q->qualifyColumn($column));

        $q->whereRaw("{$wrapped} like ? escape '" . self::ESCAPE . "'", ["%{$escaped}%"], $boolean);
    }
}
