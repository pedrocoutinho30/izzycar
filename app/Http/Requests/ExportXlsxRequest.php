<?php

namespace App\Http\Requests;

use App\Exports\ExportDefinition;
use App\Exports\ExportRegistry;
use App\Models\Expense;
use App\Models\Legalization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Pedido de exportação XLSX: fields[] (chaves do catálogo, pela ordem das
 * colunas) + os filtros da listagem. O tipo vem da rota (defaults), nunca do
 * utilizador. Sem fields[], usam-se os campos por defeito.
 */
class ExportXlsxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Definição do módulo, ou 404 se o tipo não está no registo. */
    public function definition(): ExportDefinition
    {
        return ExportRegistry::get((string) $this->route('tipo')) ?? abort(404);
    }

    public function rules(): array
    {
        return [
            'fields' => ['nullable', 'array', 'max:100'],
            'fields.*' => ['string', 'distinct', Rule::in($this->definition()->keys())],
            'search' => ['nullable', 'string', 'max:255'],
            'client_type' => ['nullable', 'string', 'max:50'],
            'lead_source' => ['nullable', 'string', 'max:100'],
            'lead_status' => ['nullable', 'string', 'max:50'],
            'owner_id' => ['nullable', 'integer'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'estado' => ['nullable', 'string', Rule::in(array_keys(Legalization::ESTADOS))],
            // Movimentos (os mesmos filtros e listas da listagem)
            'movement_type' => ['nullable', 'string', Rule::in(array_keys(Expense::movementTypes()))],
            'category' => ['nullable', 'string', Rule::in(array_keys(Expense::categories()))],
            // Viaturas: o estado aceita qualquer texto, como a listagem (valor desconhecido = sem resultados)
            'status' => $this->route('tipo') === 'vehicles'
                ? ['nullable', 'string', 'max:50']
                : ['nullable', 'string', Rule::in(array_keys(Expense::statuses()))],
            'fuel' => ['nullable', 'string', 'max:50'],
            'v3_vehicle_id' => ['nullable', 'integer', 'min:1'],
            'client_id' => ['nullable', 'integer', 'min:1'],
            'year' => ['nullable', 'string', 'regex:/^(all|\d{4})$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'fields.array' => 'A lista de campos a exportar é inválida.',
            'fields.*.in' => 'Campo de exportação desconhecido: :input.',
            'fields.*.distinct' => 'O campo :input foi indicado mais do que uma vez.',
            'fields.*.string' => 'A lista de campos a exportar é inválida.',
        ];
    }

    /** @return list<string>|null */
    public function fieldKeys(): ?array
    {
        return $this->validated('fields');
    }

    /** Só os filtros que o módulo conhece (os mesmos da listagem). */
    public function filters(): array
    {
        $definition = $this->definition();

        return $definition->normalizeFilters($this->safe()->only($definition->filterKeys()));
    }
}
