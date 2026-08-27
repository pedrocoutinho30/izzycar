<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TransportQuote;
use App\Models\Supplier;
use App\Models\Brand;
use App\Services\GeocodingService;
use Illuminate\Http\Request;

class TransportQuoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TransportQuote::with('supplier');

        // Filtro por data
        if ($request->filled('date_from')) {
            $query->where('quote_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('quote_date', '<=', $request->date_to);
        }

        // Filtro por transportadora
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filtro por tipo de carro
        if ($request->filled('car_type')) {
            $query->where('car_type', $request->car_type);
        }

        // Filtro por localização de origem (texto livre, já que cidade/país/código
        // postal agora são um único campo)
        if ($request->filled('origin_location')) {
            $query->where('origin_location', 'like', '%' . $request->origin_location . '%');
        }

        $quotes = $query->orderBy('quote_date', 'desc')->paginate(20)->withQueryString();
        $suppliers = Supplier::orderBy('company_name')->get();

        // Opções de filtro construídas a partir dos dados que já existem
        $carTypes = TransportQuote::whereNotNull('car_type')->distinct()->orderBy('car_type')->pluck('car_type');

        return view('admin.v2.transport-quotes.index', compact('quotes', 'suppliers', 'carTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('company_name')->get();
        $brands = Brand::with(['models' => function ($query) {
            $query->orderBy('name');
        }])->orderBy('name')->get();

        return view('admin.v2.transport-quotes.create', compact('suppliers', 'brands'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'car_type' => 'nullable|string|max:100',
            'origin_location' => 'required|string|max:500',
            'origin_latitude' => 'nullable|numeric|between:-90,90',
            'origin_longitude' => 'nullable|numeric|between:-180,180',
            'supplier_id' => 'required|exists:suppliers,id',
            'price' => 'required|numeric|min:0',
            'quote_date' => 'required|date',
            'estimated_delivery_days' => 'nullable|integer|min:0',
            'observations' => 'nullable|string',
        ]);

        // Coordenadas fixas de Oliveira de Azeméis
        $validated['destination_city'] = 'Oliveira de Azeméis';
        $validated['destination_country'] = 'Portugal';
        $validated['destination_latitude'] = 40.8397;
        $validated['destination_longitude'] = -8.4775;

        TransportQuote::create($validated);

        return redirect()->route('admin.transport-quotes.index')
            ->with('success', 'Transporte criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $quote = TransportQuote::with('supplier')->findOrFail($id);
        return view('admin.v2.transport-quotes.show', compact('quote'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $quote = TransportQuote::findOrFail($id);
        $suppliers = Supplier::orderBy('company_name')->get();
        $brands = Brand::with(['models' => function ($query) {
            $query->orderBy('name');
        }])->orderBy('name')->get();

        return view('admin.v2.transport-quotes.edit', compact('quote', 'suppliers', 'brands'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $quote = TransportQuote::findOrFail($id);

        $validated = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'car_type' => 'nullable|string|max:100',
            'origin_location' => 'required|string|max:500',
            'origin_latitude' => 'nullable|numeric|between:-90,90',
            'origin_longitude' => 'nullable|numeric|between:-180,180',
            'supplier_id' => 'required|exists:suppliers,id',
            'price' => 'required|numeric|min:0',
            'quote_date' => 'required|date',
            'estimated_delivery_days' => 'nullable|integer|min:0',
            'observations' => 'nullable|string',
        ]);

        $quote->update($validated);

        return redirect()->route('admin.transport-quotes.index')
            ->with('success', 'Transporte atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $quote = TransportQuote::findOrFail($id);
        $quote->delete();

        return redirect()->route('admin.transport-quotes.index')
            ->with('success', 'Transporte removido com sucesso!');
    }

    /**
     * Get quotes data for map visualization
     */
    public function getMapData(Request $request)
    {
        $query = TransportQuote::with('supplier')
            ->whereNotNull('origin_latitude')
            ->whereNotNull('origin_longitude');

        // Filtro por marca
        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        // Filtro por modelo
        if ($request->filled('model')) {
            $query->where('model', $request->model);
        }

        // Filtro por transportadora
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filtro por tipo de carro
        if ($request->filled('car_type')) {
            $query->where('car_type', $request->car_type);
        }

        $quotes = $query->get();

        $data = $quotes->map(function ($quote) {
            return [
                'id' => $quote->id,
                'brand' => $quote->brand,
                'model' => $quote->model,
                'car_type' => $quote->car_type,
                'origin_location' => $quote->origin_location,
                'lat' => (float) $quote->origin_latitude,
                'lng' => (float) $quote->origin_longitude,
                'price' => (float) $quote->price,
                'supplier' => $quote->supplier->company_name ?? $quote->supplier->name ?? 'N/A',
                'supplier_id' => $quote->supplier_id,
                'quote_date' => $quote->quote_date->format('d/m/Y'),
            ];
        });

        return response()->json($data);
    }

    /**
     * Show map view
     */
    public function map()
    {
        $suppliers = Supplier::orderBy('company_name')->get();
        $brands = TransportQuote::select('brand')->distinct()->orderBy('brand')->pluck('brand');
        $models = TransportQuote::select('model')->distinct()->orderBy('model')->pluck('model');
        $carTypes = TransportQuote::whereNotNull('car_type')->distinct()->orderBy('car_type')->pluck('car_type');

        return view('admin.v2.transport-quotes.map', compact('suppliers', 'brands', 'models', 'carTypes'));
    }

    /**
     * Obtém latitude/longitude a partir de um texto de localização
     * (cidade, código postal, país), para preencher automaticamente o
     * formulário sem o utilizador ter de procurar as coordenadas à mão.
     */
    public function geocode(Request $request)
    {
        $request->validate([
            'location' => 'required|string|max:500',
        ]);

        $result = (new GeocodingService())->geocode($request->input('location'));

        if (!$result) {
            return response()->json(['error' => 'Não foi possível encontrar coordenadas para esta localização.'], 404);
        }

        return response()->json($result);
    }
}
