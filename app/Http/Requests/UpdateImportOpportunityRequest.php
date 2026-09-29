<?php

namespace App\Http\Requests;

/**
 * A página de detalhe grava por secções (veículo, contacto), por isso cada
 * campo só é validado se vier no pedido.
 */
class UpdateImportOpportunityRequest extends ImportOpportunityRequest
{
    public function rules(): array
    {
        return collect(parent::rules())
            ->map(fn ($rule) => array_merge(['sometimes'], (array) (is_string($rule) ? explode('|', $rule) : $rule)))
            ->all();
    }

    public function opportunityData(): array
    {
        $data = parent::opportunityData();

        foreach (['status' => 'por_contactar', 'contact_status' => 'nao_contactado'] as $field => $default) {
            if (array_key_exists($field, $data) && $data[$field] === null) {
                $data[$field] = $default;
            }
        }

        return $data;
    }
}
