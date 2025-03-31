<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Supplier;
use App\Models\VehicleBrand;
use App\Models\VehicleModel;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * Display a listing of the vehicles.
     */
    public function index()
    {
        // Recuperar todos os veículos
        $vehicles = Vehicle::all();

        return view('vehicles.index', compact('vehicles'));
    }

    /**
     * Show the form for creating a new vehicle.
     */
    public function create()
    {
        // Recuperar todos os fornecedores
        $suppliers = Supplier::all();

        return view('vehicles.form', compact('suppliers'));
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
        ]);

        $brandRef = VehicleBrand::where('name', $validatedData['brand'])->first();
        $modelRef = VehicleModel::where('name', $validatedData['model'])->first();

        $validatedData['reference'] =  $brandRef->reference . $modelRef->reference . strtoupper(substr(uniqid(), -3));



        // Criar o veículo com os dados validados
        Vehicle::create($validatedData);

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

        return view('vehicles.form', compact('vehicle', 'suppliers'));
    }

    /**
     * Update the specified vehicle in storage.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        // Validar dados do formulário
        $validatedData = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'version' => 'nullable|string|max:255',
            'year' => 'nullable|integer',
            'purchase_price' => 'required',
            'sell_price' => 'nullable',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'color' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'business_type' => 'nullable|in:novo,usado,seminovo',
        ]);

        // Atualizar o veículo com os dados validados
        $vehicle->update($validatedData);

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
