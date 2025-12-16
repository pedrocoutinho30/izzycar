<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Supplier;
use App\Models\VehicleImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Brand;
use App\Models\VehicleAttribute;;

use App\Models\VehicleAttributeValue;
use App\Models\AttributeGroup;

/**
 * VehicleV2Controller
 * 
 * Controlador para gestão de veículos
 * Funcionalidades:
 * - CRUD completo de veículos
 * - Gestão de imagens
 * - Associação com fornecedores
 * - Controlo de stock (disponível para venda)
 * - Cálculo de margem de lucro
 */
class VehicleV2Controller extends Controller
{
    /**
     * Lista todos os veículos com filtros
     */
    public function index(Request $request)
    {
        // Query base com relacionamentos
        $query = Vehicle::with(['supplier', 'images']);

        // Filtro de pesquisa (referência, marca, modelo, matrícula)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('registration', 'like', "%{$search}%")
                    ->orWhere('vin', 'like', "%{$search}%");
            });
        }

        // Filtro por marca
        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        // Filtro por combustível
        if ($request->filled('fuel')) {
            $query->where('fuel', $request->fuel);
        }

        // Filtro por disponibilidade
        if ($request->filled('availability')) {
            if ($request->availability === 'available') {
                $query->doesntHave('sale');
            } elseif ($request->availability === 'sold') {
                $query->has('sale');
            }
        }

        // Filtro por visibilidade online
        if ($request->filled('show_online')) {
            $query->where('show_online', $request->show_online);
        }

        // Paginação
        $vehicles = $query->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        // Estatísticas
        $totalVehicles = Vehicle::count();
        $availableVehicles = Vehicle::doesntHave('sale')->count();
        $soldVehicles = Vehicle::has('sale')->count();
        $onlineVehicles = Vehicle::where('show_online', 1)->count();

        $stats = [
            [
                'title' => 'Total de Veículos',
                'value' => $totalVehicles,
                'color' => 'primary',
                'icon' => 'bi-car-front'
            ],
            [
                'title' => 'Disponíveis',
                'value' => $availableVehicles,
                'color' => 'success',
                'icon' => 'bi-check-circle'
            ],
            [
                'title' => 'Vendidos',
                'value' => $soldVehicles,
                'color' => 'info',
                'icon' => 'bi-cart-check'
            ],
            [
                'title' => 'Online',
                'value' => $onlineVehicles,
                'color' => 'warning',
                'icon' => 'bi-globe'
            ]
        ];

        // Lista de marcas únicas para filtro
        $brands = Vehicle::select('brand')->distinct()->orderBy('brand')->pluck('brand');

        return view('admin.v2.vehicles.index', compact('vehicles', 'stats', 'brands'));
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('company_name')->get();
        $brands = Brand::with(['models' => function ($query) {
            $query->orderBy('name');
        }])->get();
        $groupOrder = AttributeGroup::orderBy('order')->get()->pluck('name')->toArray();
        $attributes = VehicleAttribute::orderBy('order')->get()
            ->groupBy('attribute_group')
            ->sortBy(function ($group, $key) use ($groupOrder) {
                return array_search($key, $groupOrder);
            });
        return view('admin.v2.vehicles.form', compact('suppliers', 'brands', 'attributes', 'groupOrder'));
    }

    /**
     * Guarda novo veículo
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'version' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'purchase_price' => 'nullable|numeric|min:0',
            'sell_price' => 'nullable|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'color' => 'nullable|string|max:100',
            'purchase_type' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'business_type' => 'nullable|string|max:255',
            'kilometers' => 'nullable|integer|min:0',
            'power' => 'nullable|integer|min:0',
            'cylinder_capacity' => 'nullable|integer|min:0',
            'consigned_vehicle' => 'nullable|boolean',
            'fuel' => 'nullable|string|max:100',
            'vin' => 'nullable|string|max:100',
            'manufacture_date' => 'nullable|date',
            'register_date' => 'nullable|date',
            'available_to_sell_date' => 'nullable|date',
            'registration' => 'nullable|string|max:50',
            'show_online' => 'nullable|boolean',
            // Imagens
            'new_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'warranty_price' => 'nullable|numeric|min:0',
            'warranty_start_date' => 'nullable|date',
            'warranty_end_date' => 'nullable|date',
            'warranty_type' => 'nullable|string|max:255',
            'payment_type' => 'nullable|string|max:255',
        ]);
        $validated['reference'] = strtoupper(substr(uniqid(), -3));
        // Criar veículo
        $vehicle = Vehicle::create($validated);

        // Processar atributos personalizados (extras, características)
        if ($request->has('attributes')) {
            foreach ($request->input('attributes', []) as $attributeId => $value) {
                // Ignorar valores vazios
                if ($value === null || $value === '') {
                    continue;
                }

                // Criar registo de atributo
                VehicleAttributeValue::create([
                    'vehicle_id' => $vehicle->id,
                    'attribute_id' => $attributeId,
                    'value' => is_array($value) ? json_encode($value) : $value,
                ]);
            }
        }

        // Processar imagens se existirem
        if ($request->hasFile('new_images')) {
            $this->handleImageUpload($request, $vehicle);
        }

        return redirect()->route('admin.v2.vehicles.index')
            ->with('success', 'Veículo criado com sucesso!');
    }

    /**
     * Formulário de edição
     */
    public function edit($id)
    {
        $vehicle = Vehicle::with(['supplier', 'images'])->findOrFail($id);
        $suppliers = Supplier::orderBy('company_name')->get();
        $brands = Brand::with(['models' => function ($query) {
            $query->orderBy('name');
        }])->get();

        // Obter atributos agrupados e ordenados
        $groupOrder = AttributeGroup::orderBy('order')->get()->pluck('name')->toArray();
        $attributes = VehicleAttribute::orderBy('order')->get()
            ->groupBy('attribute_group')
            ->sortBy(function ($group, $key) use ($groupOrder) {
                return array_search($key, $groupOrder);
            });

        // Mapear valores de atributos por ID para fácil acesso
        $attributeValues = $vehicle->attributeValues->keyBy('attribute_id');


        return view('admin.v2.vehicles.form', compact('vehicle', 'suppliers', 'brands', 'attributes', 'groupOrder', 'attributeValues'));
    }

    /**
     * Atualiza veículo
     */
    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $validated = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'version' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'purchase_price' => 'nullable|numeric|min:0',
            'sell_price' => 'nullable|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'color' => 'nullable|string|max:100',
            'purchase_type' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'business_type' => 'nullable|string|max:255',
            'kilometers' => 'nullable|integer|min:0',
            'power' => 'nullable|integer|min:0',
            'cylinder_capacity' => 'nullable|integer|min:0',
            'consigned_vehicle' => 'nullable|boolean',
            'fuel' => 'nullable|string|max:100',
            'vin' => 'nullable|string|max:100',
            'manufacture_date' => 'nullable|date',
            'register_date' => 'nullable|date',
            'available_to_sell_date' => 'nullable|date',
            'registration' => 'nullable|string|max:50',
            'show_online' => 'nullable|boolean',
            // Imagens
            'new_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'images_to_remove' => 'nullable|string',
            'images_order' => 'nullable|string',
            'warranty_price' => 'nullable|numeric|min:0',
            'warranty_start_date' => 'nullable|date',
            'warranty_end_date' => 'nullable|date',
            'warranty_type' => 'nullable|string|max:255',
            'payment_type' => 'nullable|string|max:255',
        ]);

        // Atualizar veículo
        $vehicle->update($validated);
        // Remover atributos antigos
        $vehicle->attributeValues()->delete();

        // Processar novos atributos
        if ($request->has('attributes')) {
            foreach ($request->input('attributes', []) as $attributeId => $value) {
                if ($value === null || $value === '') {
                    continue;
                }

                VehicleAttributeValue::create([
                    'vehicle_id' => $vehicle->id,
                    'attribute_id' => $attributeId,
                    'value' => is_array($value) ? json_encode($value) : $value,
                ]);
            }
        }
        // Processar remoção de imagens
        if ($request->filled('images_to_remove')) {
            $imagesToRemove = explode(',', $request->images_to_remove);
            foreach ($imagesToRemove as $imageId) {
                $image = VehicleImage::find($imageId);
                if ($image && $image->vehicle_id == $vehicle->id) {
                    // Eliminar do storage
                    if (Storage::disk('public')->exists($image->path)) {
                        Storage::disk('public')->delete($image->path);
                    }
                    $image->delete();
                }
            }
        }

        // Processar reordenação de imagens
        if ($request->filled('images_order')) {
            $order = explode(',', $request->images_order);
            foreach ($order as $index => $imageId) {
                VehicleImage::where('id', $imageId)
                    ->where('vehicle_id', $vehicle->id)
                    ->update(['order' => $index + 1]);
            }
        }

        // Processar novas imagens se existirem
        if ($request->hasFile('new_images')) {
            $this->handleImageUpload($request, $vehicle);
        }

        return redirect()->route('admin.v2.vehicles.index')
            ->with('success', 'Veículo atualizado com sucesso!');
    }

    /**
     * Elimina veículo
     */
    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);

        // Verificar se o veículo tem venda associada
        if ($vehicle->sale) {
            return redirect()->route('admin.v2.vehicles.index')
                ->with('error', 'Não é possível eliminar um veículo que já foi vendido!');
        }

        // Eliminar imagens do storage
        foreach ($vehicle->images as $image) {
            if (Storage::exists($image->path)) {
                Storage::delete($image->path);
            }
            $image->delete();
        }

        $vehicle->delete();

        return redirect()->route('admin.v2.vehicles.index')
            ->with('success', 'Veículo eliminado com sucesso!');
    }

    /**
     * Processa o upload de imagens do veículo
     * 
     * @param Request $request
     * @param Vehicle $vehicle
     * @return void
     */
    private function handleImageUpload(Request $request, Vehicle $vehicle)
    {
        if ($request->hasFile('new_images')) {
            $currentMaxOrder = $vehicle->images()->max('order') ?? 0;

            foreach ($request->file('new_images') as $index => $image) {
                // Guardar imagem no storage
                $path = $image->store('vehicles/' . $vehicle->id, 'public');

                // Criar registo na base de dados
                VehicleImage::create([
                    'vehicle_id' => $vehicle->id,
                    'path' => $path,
                    'order' => $currentMaxOrder + $index + 1
                ]);
            }
        }
    }
}
