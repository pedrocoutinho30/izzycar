<?php

namespace App\Marketing;

use App\Models\V3Vehicle;

/**
 * Instantâneo dos dados de uma viatura V3 para um post. Só inclui o que existe:
 * campo sem valor não aparece (nada é inventado nem calculado).
 */
class VehiclePostData
{
    /** Chaves dos atributos que contam como equipamento (grupo "Equipamento"). */
    private const EQUIPMENT_GROUP = 'Equipamento';

    public static function fromVehicle(V3Vehicle $v): array
    {
        $v->loadMissing(['photos', 'attributeValues.attribute']);

        $data = [
            'vehicle_id' => $v->id,
            'reference' => $v->reference,
            'status' => $v->status,
            'brand' => $v->brand,
            'model' => $v->model,
            'version' => $v->version,
            'year' => $v->year,
            'kilometers' => $v->kilometers,
            'fuel' => $v->fuel,
            'power' => $v->power,
        ];

        if ($v->asking_price !== null && (float) $v->asking_price > 0) {
            $data['price'] = (float) $v->asking_price;
        }

        $equipment = [];
        foreach ($v->attributeValues as $av) {
            $attr = $av->attribute;
            $value = trim((string) $av->value);
            if (! $attr || $value === '' || $value === '0') {
                continue;
            }
            if ($attr->key === 'tipo_caixa') {
                $data['transmission'] = $value;
                $equipment[] = $value;
            } elseif ($attr->attribute_group === self::EQUIPMENT_GROUP) {
                $equipment[] = $attr->type === 'boolean' ? $attr->name : $value;
            }
        }
        $data['equipment'] = array_values(array_unique($equipment));

        // Capa primeiro; depois por ordem.
        $data['photos'] = $v->photos
            ->sortBy([['is_cover', 'desc'], ['order_position', 'asc']])
            ->map(fn ($p) => [
                'id' => $p->id,
                'path' => $p->path,
                'focal_x' => $p->focal_x,
                'focal_y' => $p->focal_y,
                'is_cover' => (bool) $p->is_cover,
            ])->values()->all();

        $data['captured_at'] = now()->toIso8601String();

        return $data;
    }

    /** @throws \DomainException */
    public static function assertTemplateAllowed(array $data, string $templateKey): void
    {
        $status = $data['status'] ?? null;

        if ($templateKey === 'vendido' && $status !== 'vendido') {
            throw new \DomainException('O template "Vendido / Entregue" só pode ser usado com viaturas vendidas.');
        }
        if ($templateKey === 'novo-stock' && $status !== 'em_stock') {
            throw new \DomainException('O template "Novo em stock" só pode ser usado com viaturas em stock.');
        }
    }
}
