<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\V3Vehicle;
use App\Models\VehicleAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use \App\Models\Page;
use App\Models\Testimonial;

class VehiclesController extends Controller
{

    public function index()
    {

        $reviews = Testimonial::where('published', true)->orderBy('review_date', 'desc')->get();
        
       // arredonda com 2 casas decimais
        $media = $reviews->count()
                                ? round($reviews->avg('rating'), 1)
                                : 0;
        $vehicles = Cache::remember('v3vehicles', 600, function () {
            return V3Vehicle::with(['photos' => fn ($q) => $q->where('is_cover', true)->limit(1)])
                ->where('show_online', true)
                ->orderByRaw("CASE status WHEN 'em_stock' THEN 1 WHEN 'reservado' THEN 2 WHEN 'vendido' THEN 3 ELSE 4 END")
                ->orderByRaw('purchase_date IS NULL')
                ->orderBy('purchase_date', 'desc')
                ->get();
        });

        $vehicles_count = Cache::remember('v3vehicles_count', 600, function () use ($vehicles) {
            return $vehicles->count();
        });

        $last_vehicles = Cache::remember('v3last_vehicles', 600, function () use ($vehicles) {
            return $vehicles->sortByDesc('created_at')->take(5);
        });

        $page = Page::where('slug', 'homepage')
            ->with('contents')
            ->firstOrFail();
        return view('frontend.index', compact('vehicles_count', 'last_vehicles', 'vehicles', 'page', 'reviews', 'media' ));
    }

    public function vehicles(Request $request)
    {
        // Gerar chave única de cache a partir dos filtros da querystring
        $cacheKey = 'vehicles_' . md5(json_encode($request->all()));
        $vehicles = Cache::remember($cacheKey, 600, function () use ($request) {
            $query = V3Vehicle::query()
                ->with('photos')
                ->where('show_online', true); //->where('status', '!=', 'vendido')

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
            if ($request->filled('price_max')) {
                $query->where('asking_price', '<=', (float) $request->price_max);
            }
            if ($request->filled('km_max')) {
                $query->where('kilometers', '<=', (int) $request->km_max);
            }
            return $query
                ->orderByRaw("CASE status WHEN 'em_stock' THEN 1 WHEN 'reservado' THEN 2 WHEN 'vendido' THEN 3 ELSE 4 END")
                ->orderByRaw('purchase_date IS NULL')
                ->orderBy('purchase_date', 'desc')
                ->get();
        });

        return view('frontend.vehicles-list', compact('vehicles'));
    }

    public function filteredVehicles(Request $request)
    {
        // Gera chave única com base nos filtros aplicados
        $cacheKey = 'filtered_vehicles_' . md5(json_encode($request->all()));

        // Armazena no cache por 10 minutos (600 segundos)
        $vehicles = Cache::remember($cacheKey, 600, function () use ($request) {
            $query = V3Vehicle::query()
                ->with('photos')
                ->where('show_online', true)
                ;

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
            if ($request->filled('price_max')) {
                $query->where('asking_price', '<=', (float) $request->price_max);
            }
            if ($request->filled('km_max')) {
                $query->where('kilometers', '<=', (int) $request->km_max);
            }

            return $query
                ->orderByRaw("CASE status WHEN 'em_stock' THEN 1 WHEN 'reservado' THEN 2 WHEN 'vendido' THEN 3 ELSE 4 END")
                ->orderByRaw('purchase_date IS NULL')
                ->orderBy('purchase_date', 'desc')
                ->get();
        });

        return response()->json($vehicles->map(function ($v) {
            $cover = $v->coverPhoto;
            return [
                'id'           => $v->id,
                'reference'    => $v->reference,
                'brand'        => $v->brand,
                'model'        => $v->model,
                'sub_model'    => $v->sub_model,
                'version'      => $v->version,
                'year'         => $v->year,
                'fuel'         => $v->fuel,
                'kilometers'   => $v->kilometers,
                'asking_price' => $v->asking_price,
                'status'        => $v->status,
                'cover_url'     => $cover ? asset('storage/' . $cover->path) : null,
                'cover_focal_x' => $cover ? ($cover->focal_x ?? 50) : 50,
                'cover_focal_y' => $cover ? ($cover->focal_y ?? 50) : 50,
                'url'          => route('vehicles.details', [
                    'brand' => Str::slug($v->brand ?? ''),
                    'model' => Str::slug($v->model ?? ''),
                    'id'    => $v->reference,
                ]),
            ];
        }));
    }

    public function vehicleDetails($brand, $model, $id)
    {

        $vehicle = V3Vehicle::with(['photos', 'attributeValues'])->where('reference', $id)->firstOrFail();
        $attributes = [];

        $potencia = "";
        $caixa = "";
        $cilindrada = "";
        $autonomia = "";
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
            if ($attribute->key == 'autonomia_eletrica') {
                $autonomia = $attributeValue->value;
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
            Str::slug($vehicle->brand ?? '') !== $brand ||
            Str::slug($vehicle->model ?? '') !== $model
        ) {
            abort(404);
        }

        $last_vehicles = V3Vehicle::with(['photos' => fn ($q) => $q->where('is_cover', true)->limit(1)])
            ->where('show_online', true)
            ->orderByRaw("FIELD(status, 'em_stock', 'reservado', 'vendido')")
            ->latest()->take(6)->get();


        return view('frontend.vehicles-detail', compact('vehicle', 'attributes', 'potencia', 'caixa', 'cilindrada', 'autonomia', 'last_vehicles'));
    }

    // para os filtros
    public function modelsByBrand(Request $request)
    {
        $brand = $request->get('brand');

        $cacheKey = "models_by_brand_" . md5($brand);

        $models = Cache::remember($cacheKey, 600, function () use ($brand) {
            return V3Vehicle::where('brand', $brand)
                ->where('show_online', true)->where('status', '!=', 'vendido')
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
            return V3Vehicle::where('brand', $brand)
                ->where('show_online', true)->where('status', '!=', 'vendido')
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
            $query = V3Vehicle::query()->where('show_online', true)->where('status', '!=', 'vendido');

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
            $query = V3Vehicle::query()->where('show_online', true)->where('status', '!=', 'vendido');

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
