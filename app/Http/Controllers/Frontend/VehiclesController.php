<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class VehiclesController extends Controller
{

    public function index()
    {


        $vehicles = Cache::remember('vehicles', 600, function () {
            return Vehicle::where('show_online', true)->get();
        });

        $vehicles_count = Cache::remember('vehicles_count', 600, function () use ($vehicles) {
            return $vehicles->count();
        });

        $last_vehicles = Cache::remember('last_vehicles', 600, function () use ($vehicles) {
            return $vehicles->sortByDesc('created_at')->take(5);
        });

        return view('frontend.index', compact('vehicles_count', 'last_vehicles', 'vehicles'));
    }

    public function vehicles(Request $request)
    {
        // Gerar chave única de cache a partir dos filtros da querystring
        $cacheKey = 'vehicles_' . md5(json_encode($request->all()));

        $vehicles = Cache::remember($cacheKey, 600, function () use ($request) {
            $query = Vehicle::query()->where('show_online', true);

            if ($request->brand) {
                $query->where('brand', $request->brand);
            }
            if ($request->model) {
                $query->where('model', $request->model);
            }
            if ($request->year) {
                $query->where('year', $request->year);
            }
            if ($request->fuel) {
                $query->where('fuel', $request->fuel);
            }
            // if ($request->box_type) {
            //     $query->where('box_type', $request->box_type);
            // }
            // if ($request->kilometers_range) {
            //     [$min, $max] = explode('-', str_replace('+', '', $request->kilometers_range));
            //     $query->whereBetween('kilometers', [$min, $max ?? 9999999]);
            // }

            return $query->get();
        });

        return view('frontend.vehicles-list', compact('vehicles'));
    }

    public function filteredVehicles(Request $request)
    {
        // Gera chave única com base nos filtros aplicados
        $cacheKey = 'filtered_vehicles_' . md5(json_encode($request->all()));

        // Armazena no cache por 10 minutos (600 segundos)
        $vehicles = Cache::remember($cacheKey, 600, function () use ($request) {
            $query = Vehicle::query()->with('images')->where('show_online', true);

            if ($request->filled('brand')) {
                $query->where('brand', $request->brand);
            }
            if ($request->filled('model')) {
                $query->where('model', $request->model);
            }
            if ($request->filled('year')) {
                $query->where('year', $request->year);
            }
            if ($request->filled('fuel')) {
                $query->where('fuel', $request->fuel);
            }
            if ($request->filled('kilometers_range')) {
                $range = explode('-', $request->kilometers_range);
                if (count($range) === 2) {
                    $query->whereBetween('kilometers', [$range[0], $range[1]]);
                } elseif (strpos($request->kilometers_range, '+')) {
                    $min = intval(str_replace('+', '', $request->kilometers_range));
                    $query->where('kilometers', '>=', $min);
                }
            }

            return $query->get();
        });

        return response()->json($vehicles);
    }

    public function vehicleDetails($brand, $model, $id)
    {

        $vehicle = Vehicle::where('reference', $id)->firstOrFail();
        $attributes = [];

        $potencia = "";
        $caixa = "";
        $cilindrada = "";
        foreach ($vehicle->attributeValues as $attributeValue) {


            $attribute = VehicleAttribute::find($attributeValue->attribute_id);
            if (!$attribute) continue;

            $group = $attribute->attribute_group ?? 'Outros'; // fallback caso esteja vazio
            $name = $attribute->name;
            if ($attribute->key == 'potencia') {
                $potencia = $attributeValue->value;
            }

            if ($attribute->key  == 'tipo_caixa') {
                $caixa = $attributeValue->value;
            }
            if ($attribute->key == 'cilindrada') {
                $cilindrada = $attributeValue->value;
            }
            // Inicializa o grupo se não existir
            if (!isset($attributes[$group])) {
                $attributes[$group] = [];
            }
            if ($attribute->type == 'select') {
                $attributes[$group][$name] = $attributeValue->value;
                continue;
            } elseif ($attribute->type == 'boolean') {
                $attributes[$group][$name] = $name;
                continue;
            } else if ($attribute->type == 'text' || $attribute->type == 'number') {
                $attributes[$group][$name] = $attributeValue->value;
                continue;
            }
            // Adiciona o nome do atributo e o valor no grupo correto
            $attributes[$group][$name] = $attributeValue->value;
        }

        $order = [
            'Dados do Veículo',
            'Características Técnicas',
            'Segurança & Desempenho',
            'Conforto & Multimédia',
            'Equipamento Interior',
            'Equipamento Exterior',
            'Outros Extras'
        ];

        // Função para ordenar o array associativo $attributes
        uksort($attributes, function ($a, $b) use ($order) {
            $posA = array_search($a, $order);
            $posB = array_search($b, $order);

            // Se algum grupo não estiver na lista, joga ele para o fim
            if ($posA === false) $posA = count($order);
            if ($posB === false) $posB = count($order);

            return $posA <=> $posB;
        });

        // Valida se os slugs correspondem ao veículo
        if (
            Str::slug($vehicle->brand) !== $brand ||
            Str::slug($vehicle->model) !== $model
        ) {
            abort(404);
        }

        $last_vehicles = Vehicle::get()->where('show_online', true)->sortByDesc('created_at')->take(5);


        return view('frontend.vehicles-detail', compact('vehicle', 'attributes', 'potencia', 'caixa', 'cilindrada', 'last_vehicles'));
    }

    // para os filtros
   public function modelsByBrand(Request $request)
{
    $brand = $request->get('brand');

    $cacheKey = "models_by_brand_" . md5($brand);

    $models = Cache::remember($cacheKey, 600, function () use ($brand) {
        return Vehicle::where('brand', $brand)
            ->pluck('model')
            ->unique()
            ->values();
    });

    return response()->json($models);
}

public function yearsByBrand(Request $request)
{
    $brand = $request->get('brand');

    $cacheKey = "years_by_brand_" . md5($brand);

    $years = Cache::remember($cacheKey, 600, function () use ($brand) {
        return Vehicle::where('brand', $brand)
            ->pluck('year')
            ->unique()
            ->sortDesc()
            ->values();
    });

    return response()->json($years);
}

public function yearsByBrandModel(Request $request)
{
    $brand = $request->get('brand');
    $model = $request->get('model');

    $cacheKey = "years_by_brand_model_" . md5($brand . '_' . $model);

    $years = Cache::remember($cacheKey, 600, function () use ($brand, $model) {
        $query = Vehicle::query();

        if ($brand) {
            $query->where('brand', $brand);
        }

        if ($model) {
            $query->where('model', $model);
        }

        return $query->pluck('year')->unique()->sortDesc()->values();
    });

    return response()->json($years);
}

public function fuelsByBrandModelYear(Request $request)
{
    $brand = $request->get('brand');
    $model = $request->get('model');
    $year = $request->get('year');

    $cacheKey = "fuels_by_brand_model_year_" . md5($brand . '_' . $model . '_' . $year);

    $fuels = Cache::remember($cacheKey, 600, function () use ($brand, $model, $year) {
        $query = Vehicle::query();

        if ($brand) {
            $query->where('brand', $brand);
        }

        if ($model) {
            $query->where('model', $model);
        }

        if ($year) {
            $query->where('year', $year);
        }

        return $query->pluck('fuel')->unique()->values();
    });

    return response()->json($fuels);
}
}
