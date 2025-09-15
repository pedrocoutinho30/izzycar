<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Vehicle;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\VehicleAttribute;
use App\Models\VehicleAttributeValue;
use App\Models\Partner;
use Intervention\Image\ImageManager;


class VehicleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the vehicles.
     */
    public function index()
    {
        // Recuperar todos os veículos
        $vehicles = Vehicle::paginate(10);

        return view('vehicles.index', compact('vehicles'));
    }

    /**
     * Show the form for creating a new vehicle.
     */
    public function create()
    {

        // aqui quero criar logo um registo veiculo apenas com referência e enviar para a pagina de edição
        $reference = $this->generateUniqueCode();
        $vehicle = Vehicle::create(['reference' => $reference, 'brand' => '', 'model' => '']);

        // Redirecionar para a página de edição do veículo recém-criado
        return redirect()->route('vehicles.edit', $vehicle->id)->with('success', 'Veículo criado com sucesso! Agora, por favor, preencha os detalhes.');
        // Recuperar todos os fornecedores
        $suppliers = Supplier::all();

        $attributes = VehicleAttribute::all()->groupBy('attribute_group');
        $brands = Brand::with(['models' => function ($query) {
            $query->orderBy('name');
        }])->get();



        return view('vehicles.form', compact('suppliers', 'attributes', 'brands'));
    }
    function generateUniqueCode()
    {
        do {
            // Gerar código aleatório 3 letras + 3 números
            $letters = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 3);
            $numbers = substr(str_shuffle('0123456789'), 0, 3);
            $code = $letters . $numbers;
            // Verifica se já existe no banco
            $exists = Vehicle::where('reference', $code)->exists();
        } while ($exists);

        return $code;
    }
    /**
     * Store a newly created vehicle in storage.
     */
    public function store(Request $request)
    {
        // Validar dados do formulário
        $validatedData = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'version' => 'nullable|string|max:255',
            'year' => 'nullable|integer',
            'purchase_price' => 'required|numeric',
            'sell_price' => 'nullable|numeric',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'color' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'business_type' => 'nullable|in:novo,usado,seminovo',
            'kilometers' => 'nullable|integer',
            'power' => 'nullable|integer',
            'cylinder_capacity' => 'nullable|integer',
            'consigned_vehicle' => 'nullable|boolean',
            'fuel' => 'nullable|in:Diesel,Gasolina,Elétrico,Hibrido-plug-in/Gasolina,Hibrido-plug-in/Diesel,Outro',
            'vin' => 'nullable|string|max:255', // VIN não é obrigatório
            'manufacture_date' => 'nullable|date',
            'register_date' => 'nullable|date',
            'available_to_sell_date' => 'nullable|date',
            'registration' => 'nullable|string|max:255',
            'purchase_type' => 'nullable|in:Margem,Geral,Sem Iva',
            'show_online' => 'nullable|boolean',
            // 'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048', // Validação para imagens
        ]);

        $validatedData['reference'] =   strtoupper(substr(uniqid(), -3));


        // Criar o veículo com os dados validados
        $vehicle =  Vehicle::create($validatedData);

        foreach ($request->input('attributes', []) as $attributeId => $value) {
            VehicleAttributeValue::create([
                'vehicle_id' => $vehicle->id,
                'attribute_id' => $attributeId,
                'value' => is_array($value) ? json_encode($value) : $value,
            ]);
        }

        // Salvar imagens
        // if ($request->hasFile('images')) {
        //     $i = 1;
        //     foreach ($request->file('images') as $image) {
        //         $extension = $image->getClientOriginalExtension();

        //         // Nome personalizado: vehicles_brand_model_version_id_numeração
        //         $fileName = "vehicles_{$vehicle->brand}_{$vehicle->model}_{$vehicle->version}_{$vehicle->year}_{$vehicle->id}_{$i}." . $extension;

        //         // Guardar com o nome personalizado
        //         $path = $image->storeAs(
        //             "vehicles/{$vehicle->id}", // Pasta
        //             $fileName,                 // Nome
        //             'public'                   // Disco
        //         );

        //         // Gravar no banco de dados
        //         $vehicle->images()->create(['image_path' => $path]);

        //         $i++;
        //     }
        // }

        $imagesNew = $request->input('images_new', []);


        $images = explode(',', $imagesNew);

        foreach ($images as $imagePath) {
            // Gravar no banco de dados
            $vehicle->images()->create(['image_path' => $imagePath]);
        }



        return redirect()->route('vehicles.index')->with('success', 'Veículo criado com sucesso!');
    }

    /**
     * Display the specified vehicle.
     */
    public function show(Vehicle $vehicle)
    {
        return view('vehicles.show', compact('vehicle'));
    }

    /**
     * Show the form for editing the specified vehicle.
     */
    public function edit(Vehicle $vehicle)
    {
        // Recuperar todos os fornecedores
        $suppliers = Supplier::all();
        $attributes = VehicleAttribute::orderByRaw("FIELD(type, 'text', 'number', 'select', 'checkbox')")->get()->groupBy('attribute_group');

        $attributeValues = $vehicle->attributeValues->keyBy('attribute_id');

        $brands = Brand::with(['models' => function ($query) {
            $query->orderBy('name');
        }])->get();

        $expenses = $vehicle->expenses()->get();
        $partners = Partner::all();

        $existingImages = '';
        foreach ($vehicle->images as $img) {
            $existingImages .= $existingImages ? ',' . $img->image_path :  $img->image_path;
        }

        return view('vehicles.form', compact('vehicle', 'suppliers', 'attributes', 'attributeValues', 'brands', 'expenses', 'partners', 'existingImages'));
    }

    /**
     * Update the specified vehicle in storage.
     */
    public function update(Request $request, Vehicle $vehicle)
    {

        // Validar dados do formulário
        $validatedData = $request->validate([
            'show_online' => 'nullable|boolean',
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'version' => 'nullable|string|max:255',
            'year' => 'nullable|integer',
            'purchase_price' => 'required|numeric',
            'sell_price' => 'nullable|numeric',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'color' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'business_type' => 'nullable|in:novo,usado,seminovo',
            'kilometers' => 'nullable|integer',
            'power' => 'nullable|integer',
            'cylinder_capacity' => 'nullable|integer',
            'consigned_vehicle' => 'nullable|boolean',
            'fuel' => 'nullable|in:Diesel,Gasolina,Elétrico,Hibrido-plug-in/Gasolina,Hibrido-plug-in/Diesel,Outro',
            'vin' => 'nullable|string|max:255', // VIN não é obrigatório
            'manufacture_date' => 'nullable|date',
            'register_date' => 'nullable|date',
            'available_to_sell_date' => 'nullable|date',
            'registration' => 'nullable|string|max:255',
            'purchase_type' => 'nullable|in:Margem,Geral,Sem Iva',
            'images.*' => 'string', // cada item é uma string (path)
        ]);

        // Atualizar o veículo com os dados validados
        $vehicle->update($validatedData);

        // Remove os antigos
        $vehicle->attributeValues()->delete();
        foreach ($request->input('attributes', []) as $attributeId => $value) {
            if ($value === null) {
                continue; // Ignorar valores nulos
            }
            VehicleAttributeValue::create([
                'vehicle_id' => $vehicle->id,
                'attribute_id' => $attributeId,
                'value' => is_array($value) ? json_encode($value) : $value,
            ]);
        }

        // Obter arrays das imagens
        $imagesNew = $request->input('images_new', []);
        $imagesExisting = $request->input('images_existing', '');
        $imagesRemoved = $request->input('images_removed', []);

        // Converter imagens existentes para array
        $existingArray = $imagesExisting ? explode(',', $imagesExisting) : [];
        $removedArray = $imagesRemoved ? explode(',', $imagesRemoved) : [];
        // Filtrar imagens existentes removendo as que estão em imagesRemoved
        $filteredExisting = array_diff($existingArray, $removedArray);

        $filteredExistingString = $filteredExisting ? implode(',', $filteredExisting) : '';
        // Concatenar novas imagens e existentes filtradas
        $imagesNew .=  $filteredExistingString ? ", " . $filteredExistingString : '';
        $images = explode(',', $imagesNew);
        $vehicle->images()->delete();

        foreach ($images as $imagePath) {
            // Gravar no banco de dados
            $vehicle->images()->create(['image_path' => $imagePath]);
        }
        // Criar string final separada por vírgula


        // if ($request->input('images_existing')) {
        //     // Remove as antigas

        // }
        // Salvar novas imagens
        // Update - adicionar novas imagens sem apagar as antigas
        // if ($request->hasFile('images')) {
        // Descobrir quantas imagens já existem para continuar a numeração
        // $i = $vehicle->images()->count() + 1;

        // foreach ($request->file('images') as $image) {
        //     $extension = $image->getClientOriginalExtension();

        //     // Nome personalizado
        //     $fileName = "vehicles_{$vehicle->brand}_{$vehicle->model}_{$vehicle->version}_{$vehicle->year}_{$vehicle->id}_{$i}." . $extension;

        //     // Guardar com nome personalizado
        //     $path = $image->storeAs(
        //         "vehicles/{$vehicle->id}", // Pasta
        //         $fileName,                 // Nome
        //         'public'                   // Disco
        //     );

        //     // Gravar no banco de dados
        //     $vehicle->images()->create(['image_path' => $path]);

        //     $i++;
        // }


        // }

        return redirect()->route('vehicles.index')->with('success', 'Veículo atualizado com sucesso!');
    }

    /**
     * Remove the specified vehicle from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        // Deletar o veículo
        $vehicle->delete();

        return redirect()->route('vehicles.index')->with('success', 'Veículo deletado com sucesso!');
    }
}
