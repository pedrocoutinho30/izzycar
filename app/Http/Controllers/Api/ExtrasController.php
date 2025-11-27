<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttributeGroup;
use App\Models\VehicleAttribute;
use Illuminate\Http\Request;

class ExtrasController extends Controller
{
    public function index()
    {
        // Buscar todos os grupos de atributos com seus atributos
        $attributeGroups = AttributeGroup::orderBy('order')
            ->get()
            ->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'order' => $group->order,
                    'created_at' => $group->created_at,
                    'updated_at' => $group->updated_at,
                ];
            });

        // Buscar todos os atributos de veículo ordenados
        $vehicleAttributes = VehicleAttribute::orderBy('order')
            ->orderBy('name')
            ->get()
            ->map(function ($attribute) {
                return [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                    'key' => $attribute->key,
                    'type' => $attribute->type,
                    'options' => $attribute->options,
                    'order' => $attribute->order,
                    'attribute_group' => $attribute->attribute_group,
                    'field_name_autoscout' => $attribute->field_name_autoscout,
                    'field_name_mobile' => $attribute->field_name_mobile,
                    'created_at' => $attribute->created_at,
                    'updated_at' => $attribute->updated_at,
                ];
            });

        // Agrupar atributos por grupo
        $groupedAttributes = $attributeGroups->map(function ($group) use ($vehicleAttributes) {
            $group['attributes'] = $vehicleAttributes
                ->where('attribute_group', $group['id'])
                ->values()
                ->toArray();
            return $group;
        });

        // Atributos sem grupo
        $ungroupedAttributes = $vehicleAttributes
            ->whereNull('attribute_group')
            ->values()
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'attribute_groups' => $groupedAttributes,
                'ungrouped_attributes' => $ungroupedAttributes,
                'all_attributes' => $vehicleAttributes->toArray(),
            ]
        ]);
    }
}
