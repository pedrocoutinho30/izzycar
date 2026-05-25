<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttributeGroup;
use App\Models\Brand;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Legalization;
use App\Models\LegalizationDocument;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\V3Vehicle;
use App\Models\V3VehicleDocument;
use App\Models\V3VehiclePhoto;
use App\Models\VehicleAttribute;
use App\Models\VehicleAttributeValue;
use App\Services\SaleCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

class V3VehicleController extends Controller
{
    // ═══════════════════════════════════════════════════
    // INDEX / CRUD
    // ═══════════════════════════════════════════════════

    public function index(Request $request)
    {
        $query = V3Vehicle::with([
            'photos' => fn ($q) => $q->where('is_cover', true)->limit(1),
        ]);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('reference', 'like', "%$s%")
                  ->orWhere('brand', 'like', "%$s%")
                  ->orWhere('model', 'like', "%$s%")
                  ->orWhere('registration', 'like', "%$s%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('fuel')) {
            $query->where('fuel', $request->fuel);
        }

        $vehicles = $query->latest()->paginate(20)->withQueryString();

        return view('admin.v3.vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        $brands = Brand::with(['models' => fn ($q) => $q->with('submodels')->orderBy('name')])
            ->orderBy('name')->get();
        return view('admin.v3.vehicles.create', compact('brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'year'  => 'nullable|integer|min:1900|max:' . (now()->year + 2),
            'fuel'  => 'nullable|string|max:50',
        ]);

        $vehicle = V3Vehicle::create([
            'reference' => V3Vehicle::generateReference(),
            'brand'     => $request->brand,
            'model'     => $request->model,
            'year'      => $request->year,
            'fuel'      => $request->fuel,
            'status'    => 'em_stock',
        ]);

        return redirect()->route('admin.v3.vehicles.edit', $vehicle->id)
            ->with('success', 'Veículo criado. Complete a informação nas tabs abaixo.');
    }

    public function edit($id)
    {
        $vehicle = V3Vehicle::with([
            'supplier',
            'photos',
            'documents',
            'expenses'        => fn ($q) => $q->orderBy('expense_date', 'desc'),
            'sales'           => fn ($q) => $q->latest()->with('client'),
            'legalization'    => fn ($q) => $q->with('documents'),
            'attributeValues',
        ])->findOrFail($id);

        $suppliers = Supplier::orderBy('company_name')->get();
        $clients   = Client::orderBy('name')->get();
        $docTypes  = Legalization::DOCUMENTOS;
        $brands    = Brand::with(['models' => fn ($q) => $q->with('submodels')->orderBy('name')])
            ->orderBy('name')->get();

        $groupOrder      = AttributeGroup::orderBy('order')->get()->pluck('name')->toArray();
        $attributes      = VehicleAttribute::orderBy('order')->get()
            ->groupBy('attribute_group')
            ->sortBy(fn ($g, $k) => array_search($k, $groupOrder));
        $attributeValues = $vehicle->attributeValues->keyBy('attribute_id');

        return view('admin.v3.vehicles.edit', compact(
            'vehicle', 'suppliers', 'clients', 'docTypes', 'brands',
            'attributes', 'attributeValues'
        ));
    }

    public function destroy($id)
    {
        $vehicle = V3Vehicle::findOrFail($id);

        Storage::deleteDirectory("v3-vehicles/{$vehicle->id}");
        Storage::disk('public')->deleteDirectory("v3-vehicles/{$vehicle->id}");

        $vehicle->delete();

        return redirect()->route('admin.v3.vehicles.index')
            ->with('success', 'Veículo eliminado.');
    }

    // ═══════════════════════════════════════════════════
    // AUTO-SAVE (AJAX) — General + Purchase + Equipment
    // ═══════════════════════════════════════════════════

    public function saveEquipment(Request $request, $id)
    {
        $vehicle = V3Vehicle::findOrFail($id);

        // Delete existing attribute values for this V3 vehicle
        $vehicle->attributeValues()->delete();

        // Recreate from submitted checkboxes / selects
        foreach ($request->input('attributes', []) as $attributeId => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            VehicleAttributeValue::create([
                'v3_vehicle_id' => $vehicle->id,
                'attribute_id'  => (int) $attributeId,
                'value'         => is_array($value) ? json_encode($value) : $value,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Equipamento guardado.']);
    }


    public function saveGeneral(Request $request, $id)
    {
        $vehicle = V3Vehicle::findOrFail($id);

        $validated = $request->validate([
            'brand'                  => 'nullable|string|max:100',
            'model'                  => 'nullable|string|max:100',
            'sub_model'              => 'nullable|string|max:150',
            'version'                => 'nullable|string|max:100',
            'year'                   => 'nullable|integer|min:1900|max:' . (now()->year + 2),
            'month'                  => 'nullable|integer|min:1|max:12',
            'day'                    => 'nullable|integer|min:1|max:31',
            'fuel'                   => 'nullable|string|max:50',
            'kilometers'             => 'nullable|integer|min:0',
            'power'                  => 'nullable|integer|min:0',
            'cylinder_capacity'      => 'nullable|integer|min:0',
            'color'                  => 'nullable|string|max:50',
            'vin'                    => 'nullable|string|max:17',
            'registration'           => 'nullable|string|max:20',
            'manufacture_date'       => 'nullable|date',
            'register_date'          => 'nullable|date',
            'available_to_sell_date' => 'nullable|date',
            'notes'                  => 'nullable|string',
            'status'                 => 'nullable|in:em_stock,vendido,reservado',
            'asking_price'           => 'nullable|numeric|min:0',
        ]);

        $validated['show_online'] = $request->boolean('show_online');
        $validated['is_imported'] = $request->boolean('is_imported');
        if (empty($validated['month'])) {
            $validated['day'] = null;
        }
        $vehicle->update($validated);

        return response()->json(['success' => true, 'message' => 'Informação geral guardada.']);
    }

    public function savePurchase(Request $request, $id)
    {
        $vehicle = V3Vehicle::findOrFail($id);

        $validated = $request->validate([
            'supplier_id'       => 'nullable|exists:suppliers,id',
            'purchase_price'    => 'nullable|numeric|min:0',
            'purchase_date'     => 'nullable|date',
            'purchase_type'     => 'nullable|in:Geral,Margem,Sem Iva',
            'purchase_vat_rate' => 'nullable|numeric|min:0|max:100',
            'purchase_vat_paid' => 'nullable|numeric|min:0',
        ]);

        $vehicle->update($validated);

        Expense::syncFromV3Vehicle($vehicle->fresh());

        return response()->json(['success' => true, 'message' => 'Dados de compra guardados.']);
    }

    // ═══════════════════════════════════════════════════
    // EXPENSES
    // ═══════════════════════════════════════════════════

    public function storeExpense(Request $request, $id)
    {
        $vehicle = V3Vehicle::findOrFail($id);

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'movement_type'  => 'required|in:' . implode(',', array_keys(Expense::movementTypes())),
            'category'       => 'nullable|in:' . implode(',', array_keys(Expense::categories())),
            'amount'         => 'required|numeric|min:0',
            'vat_rate'       => 'nullable|numeric|min:0|max:100',
            'expense_date'   => 'required|date',
            'payment_method' => 'nullable|in:' . implode(',', array_keys(Expense::paymentMethods())),
            'status'         => 'nullable|in:' . implode(',', array_keys(Expense::statuses())),
            'observations'   => 'nullable|string',
        ]);

        $amountGross = (float) $validated['amount'];
        $vatRate     = (float) ($validated['vat_rate'] ?? 0);
        $vatAmount   = round($amountGross * $vatRate / 100, 2);
        $amountNet   = round($amountGross - $vatAmount, 2);

        Expense::create(array_merge($validated, [
            'v3_vehicle_id' => $vehicle->id,
            'amount_gross'  => $amountGross,
            'vat_amount'    => $vatAmount,
            'amount_net'    => $amountNet,
        ]));

        return back()->with('success', 'Despesa adicionada.')->with('return_tab', 'expenses');
    }

    public function destroyExpense($vehicleId, $expenseId)
    {
        $vehicle = V3Vehicle::findOrFail($vehicleId);
        $expense = Expense::where('v3_vehicle_id', $vehicle->id)->findOrFail($expenseId);
        $expense->delete();

        return back()->with('success', 'Despesa removida.')->with('return_tab', 'expenses');
    }

    // ═══════════════════════════════════════════════════
    // DOCUMENTS
    // ═══════════════════════════════════════════════════

    public function uploadDocument(Request $request, $id)
    {
        $vehicle = V3Vehicle::findOrFail($id);

        $request->validate([
            'ficheiro' => 'required|file|max:20480',
            'tipo'     => 'nullable|string|max:50',
            'titulo'   => 'nullable|string|max:255',
        ]);

        $file         = $request->file('ficheiro');
        $originalName = $file->getClientOriginalName();
        $path         = $file->store("v3-vehicles/{$vehicle->id}/documents");

        V3VehicleDocument::create([
            'v3_vehicle_id' => $vehicle->id,
            'tipo'          => $request->tipo ?: null,
            'titulo'        => $request->titulo ?: null,
            'nome_original' => $originalName,
            'caminho'       => $path,
        ]);

        return back()->with('success', 'Documento carregado.')->with('return_tab', $request->input('return_tab', 'documents'));
    }

    public function downloadDocument($vehicleId, $documentId)
    {
        $vehicle  = V3Vehicle::findOrFail($vehicleId);
        $document = V3VehicleDocument::where('v3_vehicle_id', $vehicle->id)->findOrFail($documentId);

        if (!Storage::exists($document->caminho)) {
            abort(404, 'Ficheiro não encontrado.');
        }

        return Storage::download($document->caminho, $document->nome_original);
    }

    public function destroyDocument(Request $request, $vehicleId, $documentId)
    {
        $vehicle  = V3Vehicle::findOrFail($vehicleId);
        $document = V3VehicleDocument::where('v3_vehicle_id', $vehicle->id)->findOrFail($documentId);

        Storage::delete($document->caminho);
        $document->delete();

        return back()->with('success', 'Documento eliminado.')->with('return_tab', $request->input('return_tab', 'documents'));
    }

    // ═══════════════════════════════════════════════════
    // PHOTOS
    // ═══════════════════════════════════════════════════

    public function uploadPhoto(Request $request, $id)
    {
        $vehicle = V3Vehicle::findOrFail($id);

        $request->validate([
            'fotos'   => 'required|array|min:1',
            'fotos.*' => 'image|max:5120',
        ]);

        $maxOrder  = $vehicle->photos()->max('order_position') ?? 0;
        $isFirst   = $vehicle->photos()->doesntExist();

        foreach ($request->file('fotos') as $file) {
            $path = $file->store("v3-vehicles/{$vehicle->id}/photos", 'public');
            V3VehiclePhoto::create([
                'v3_vehicle_id'  => $vehicle->id,
                'path'           => $path,
                'order_position' => ++$maxOrder,
                'is_cover'       => $isFirst,
            ]);
            $isFirst = false;
        }

        return back()->with('success', 'Foto(s) carregada(s).')->with('return_tab', 'photos');
    }

    public function destroyPhoto($vehicleId, $photoId)
    {
        $vehicle  = V3Vehicle::findOrFail($vehicleId);
        $photo    = V3VehiclePhoto::where('v3_vehicle_id', $vehicle->id)->findOrFail($photoId);
        $wasCover = $photo->is_cover;

        Storage::disk('public')->delete($photo->path);
        $photo->delete();

        if ($wasCover) {
            V3VehiclePhoto::where('v3_vehicle_id', $vehicle->id)
                ->orderBy('order_position')
                ->first()
                ?->update(['is_cover' => true]);
        }

        return back()->with('success', 'Foto eliminada.')->with('return_tab', 'photos');
    }

    public function setCoverPhoto($vehicleId, $photoId)
    {
        $vehicle = V3Vehicle::findOrFail($vehicleId);
        V3VehiclePhoto::where('v3_vehicle_id', $vehicle->id)->update(['is_cover' => false]);
        V3VehiclePhoto::where('v3_vehicle_id', $vehicle->id)->where('id', $photoId)->update(['is_cover' => true]);

        return back()->with('return_tab', 'photos');
    }

    public function setFocalPoint(Request $request, $vehicleId, $photoId)
    {
        $vehicle = V3Vehicle::findOrFail($vehicleId);
        $photo   = V3VehiclePhoto::where('v3_vehicle_id', $vehicle->id)->findOrFail($photoId);

        $request->validate([
            'focal_x' => 'required|numeric|min:0|max:100',
            'focal_y' => 'required|numeric|min:0|max:100',
        ]);

        $photo->update([
            'focal_x' => round((float) $request->focal_x, 2),
            'focal_y' => round((float) $request->focal_y, 2),
        ]);

        return response()->json(['success' => true]);
    }

    private function backupPath(string $storagePath): string
    {
        return preg_replace('/(\.[^.\/]+)$/', '_original$1', $storagePath);
    }

    public function cropPhoto(Request $request, $vehicleId, $photoId)
    {
        $vehicle = V3Vehicle::findOrFail($vehicleId);
        $photo   = V3VehiclePhoto::where('v3_vehicle_id', $vehicle->id)->findOrFail($photoId);

        $request->validate([
            'x'      => 'required|numeric|min:0',
            'y'      => 'required|numeric|min:0',
            'width'  => 'required|numeric|min:10',
            'height' => 'required|numeric|min:10',
        ]);

        $path     = storage_path('app/public/' . $photo->path);
        $backupAbs = storage_path('app/public/' . $this->backupPath($photo->path));

        if (!file_exists($path)) {
            return response()->json(['success' => false, 'message' => 'Ficheiro não encontrado.'], 404);
        }

        // Keep original backup only on first crop
        if (!file_exists($backupAbs)) {
            copy($path, $backupAbs);
        }

        $manager = new ImageManager(new GdDriver());
        $img     = $manager->read($path);

        $img->crop(
            (int) round($request->width),
            (int) round($request->height),
            (int) round($request->x),
            (int) round($request->y)
        );

        $img->save($path);

        // Reset focal point to centre after crop
        $photo->update(['focal_x' => 50.00, 'focal_y' => 50.00]);

        return response()->json([
            'success'    => true,
            'new_url'    => asset('storage/' . $photo->path) . '?v=' . time(),
            'has_backup' => true,
        ]);
    }

    public function restorePhoto($vehicleId, $photoId)
    {
        $vehicle = V3Vehicle::findOrFail($vehicleId);
        $photo   = V3VehiclePhoto::where('v3_vehicle_id', $vehicle->id)->findOrFail($photoId);

        $path      = storage_path('app/public/' . $photo->path);
        $backupAbs = storage_path('app/public/' . $this->backupPath($photo->path));

        if (!file_exists($backupAbs)) {
            return response()->json(['success' => false, 'message' => 'Backup não encontrado.'], 404);
        }

        copy($backupAbs, $path);
        unlink($backupAbs);

        $photo->update(['focal_x' => 50.00, 'focal_y' => 50.00]);

        return response()->json([
            'success'    => true,
            'new_url'    => asset('storage/' . $photo->path) . '?v=' . time(),
            'has_backup' => false,
        ]);
    }

    public function reorderPhotos(Request $request, $id)
    {
        $vehicle = V3Vehicle::findOrFail($id);
        $request->validate(['order' => 'required|array']);

        foreach ($request->order as $position => $photoId) {
            V3VehiclePhoto::where('v3_vehicle_id', $vehicle->id)
                ->where('id', $photoId)
                ->update(['order_position' => (int) $position]);
        }

        return response()->json(['success' => true]);
    }

    // ═══════════════════════════════════════════════════
    // SALE
    // ═══════════════════════════════════════════════════

    public function storeSale(Request $request, $id)
    {
        $vehicle = V3Vehicle::with('expenses')->findOrFail($id);

        if ($vehicle->sales()->exists()) {
            return back()->with('error', 'Este veículo já tem uma venda registada.')
                ->with('return_tab', 'sale');
        }

        $validated = $request->validate([
            'sale_date'      => 'required|date',
            'sale_price'     => 'required|numeric|min:0',
            'vat_type'       => 'required|string',
            'payment_method' => 'nullable|string',
            'client_id'      => 'nullable|exists:clients,id',
            'observation'    => 'nullable|string',
        ]);

        $metrics = $this->calculateSaleMetrics(
            $vehicle,
            (float) $validated['sale_price'],
            $validated['vat_type']
        );

        Sale::create([
            'v3_vehicle_id'          => $vehicle->id,
            'vehicle_id'             => null,
            'client_id'              => $validated['client_id'] ?? null,
            'sale_date'              => $validated['sale_date'],
            'sale_price'             => $validated['sale_price'],
            'vat_rate'               => $metrics['vat_rate'],
            'payment_method'         => $validated['payment_method'] ?? null,
            'observation'            => $validated['observation'] ?? null,
            'gross_margin'           => $metrics['gross_margin'],
            'net_margin'             => $metrics['net_margin'],
            'vat_paid'               => $metrics['vat_paid'],
            'vat_deducible_purchase' => $metrics['vat_recoverable'],
            'vat_settle_sale'        => $metrics['vat_settle_sale'],
            'totalCost'              => $metrics['total_cost'],
            'totalExpenses'          => $metrics['expenses_total'],
            'net_profitability'      => $metrics['net_profitability'],
            'gross_profitability'    => $metrics['gross_profitability'],
        ]);

        $vehicle->update(['status' => 'vendido']);

        return back()->with('success', 'Venda registada com sucesso.')->with('return_tab', 'sale');
    }

    public function destroySale($vehicleId, $saleId)
    {
        $vehicle = V3Vehicle::findOrFail($vehicleId);
        $sale    = Sale::where('v3_vehicle_id', $vehicle->id)->findOrFail($saleId);
        $sale->delete();

        $vehicle->update(['status' => 'em_stock']);

        return back()->with('success', 'Venda anulada.')->with('return_tab', 'sale');
    }

    public function calculatePreview(Request $request, $id)
    {
        $request->validate([
            'sale_price'  => 'required|numeric|min:0',
            'vat_type'    => 'required|string',
            'extra_costs' => 'nullable|numeric|min:0',
        ]);

        $vehicle    = V3Vehicle::with('expenses')->findOrFail($id);
        $extraCosts = (float) ($request->extra_costs ?? 0);

        $expenses = $vehicle->expenses()
            ->whereNull('source_type')
            ->where('movement_type', 'expense')
            ->where('category', '!=', 'vehicle_purchase')
            ->get();

        // Append extra simulator costs as a synthetic expense at 23% VAT
        if ($extraCosts > 0) {
            $expenses->push((object) [
                'vat_rate'     => '23',
                'amount_gross' => $extraCosts,
                'amount'       => $extraCosts,
            ]);
        }

        $metrics = SaleCalculator::compute(
            (float) $request->sale_price,
            $request->vat_type,
            (float) ($vehicle->purchase_price ?? 0),
            (float) ($vehicle->purchase_vat_paid ?? 0),
            $expenses
        );

        return response()->json($metrics);
    }

    // ═══════════════════════════════════════════════════
    // AD TEXT (Gerar Anúncio)
    // ═══════════════════════════════════════════════════

    public function generateAdText(Request $request, $id)
    {
        $vehicle = V3Vehicle::with(['attributeValues.attribute'])->findOrFail($id);

        // Collect enabled equipment attributes
        $equipment = $vehicle->attributeValues
            ->filter(fn($v) => $v->value && $v->value !== '0' && $v->value !== '' && $v->attribute)
            ->map(function ($v) {
                $attr = $v->attribute;
                if ($attr->type === 'boolean') {
                    return $attr->name;
                }
                // select / text — show as "Name: Value"
                return $attr->name . ': ' . $v->value;
            })
            ->filter()
            ->values()
            ->toArray();

        $apiKey = config('services.openai.key');

        if (empty($apiKey)) {
            return response()->json([
                'success' => true,
                'text'    => $this->buildTemplateAdText($vehicle, $equipment),
                'ai'      => false,
            ]);
        }

        // Build structured info for the prompt
        $parts = array_filter([
            $vehicle->brand,
            $vehicle->model,
            $vehicle->sub_model,
            $vehicle->version,
        ]);
        $name = implode(' ', $parts);

        $specs = array_filter([
            'Ano'          => $vehicle->year,
            'Combustível'  => $vehicle->fuel,
            'Quilómetros'  => $vehicle->kilometers ? number_format($vehicle->kilometers, 0, ',', '.') . ' km' : null,
            'Potência'     => $vehicle->power ? $vehicle->power . ' CV' : null,
            'Cilindrada'   => $vehicle->cylinder_capacity ? $vehicle->cylinder_capacity . ' cc' : null,
            'Cor'          => $vehicle->color,
        ]);
        $specsText = implode("\n", array_map(fn($k, $v) => "- $k: $v", array_keys($specs), $specs));
        $equipText = !empty($equipment)
            ? "\n\nEquipamento incluído:\n- " . implode("\n- ", $equipment)
            : '';

        $userPrompt = "Cria um anúncio profissional e apelativo em português de Portugal para a venda de um automóvel usado:\n\n"
            . "**Veículo:** $name\n\n"
            . "**Características:**\n$specsText"
            . $equipText
            . "\n\nO anúncio deve:\n"
            . "- Ser cativante e destacar os pontos fortes\n"
            . "- Ter um tom profissional adequado para OLX, AutoSapo, CustoJusto, etc.\n"
            . "- Incluir um título apelativo no início (em linha própria, sem hashtags)\n"
            . "- Ter entre 150 e 300 palavras\n"
            . "- Terminar com um convite ao contacto";

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model'       => 'gpt-4o-mini',
                'messages'    => [
                    [
                        'role'    => 'system',
                        'content' => 'És um especialista em marketing automóvel português. Crias anúncios persuasivos, honestos e profissionais para a venda de automóveis usados em plataformas portuguesas.',
                    ],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'max_tokens'  => 700,
                'temperature' => 0.72,
            ]),
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return response()->json([
                'success' => true,
                'text'    => $this->buildTemplateAdText($vehicle, $equipment),
                'ai'      => false,
            ]);
        }

        $data = json_decode($response, true);
        $text = $data['choices'][0]['message']['content'] ?? null;

        if (!$text) {
            return response()->json([
                'success' => true,
                'text'    => $this->buildTemplateAdText($vehicle, $equipment),
                'ai'      => false,
            ]);
        }

        return response()->json(['success' => true, 'text' => trim($text), 'ai' => true]);
    }

    public function saveAdText(Request $request, $id)
    {
        $vehicle   = V3Vehicle::findOrFail($id);
        $validated = $request->validate(['ad_text' => 'nullable|string|max:5000']);
        $vehicle->update(['ad_text' => $validated['ad_text'] ?? null]);

        return response()->json(['success' => true, 'message' => 'Anúncio guardado com sucesso.']);
    }

    // ═══════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ═══════════════════════════════════════════════════

    private function buildTemplateAdText(V3Vehicle $vehicle, array $equipment): string
    {
        $nameParts = array_filter([
            $vehicle->brand,
            $vehicle->model,
            $vehicle->sub_model,
            $vehicle->version,
        ]);
        $name = implode(' ', $nameParts) ?: 'Veículo';
        $year = $vehicle->year ? " ({$vehicle->year})" : '';

        $lines   = [];
        $lines[] = strtoupper($name) . $year;
        $lines[] = '';

        if ($vehicle->kilometers) {
            $lines[] = '🔢 Quilómetros: ' . number_format($vehicle->kilometers, 0, ',', '.') . ' km';
        }
        if ($vehicle->fuel)             { $lines[] = '⛽ Combustível: ' . $vehicle->fuel; }
        if ($vehicle->power)            { $lines[] = '⚡ Potência: ' . $vehicle->power . ' CV'; }
        if ($vehicle->cylinder_capacity){ $lines[] = '🔧 Cilindrada: ' . $vehicle->cylinder_capacity . ' cc'; }
        if ($vehicle->color)            { $lines[] = '🎨 Cor: ' . $vehicle->color; }

        if (!empty($equipment)) {
            $lines[] = '';
            $lines[] = '✅ Equipamento de série:';
            foreach ($equipment as $e) {
                $lines[] = '   • ' . $e;
            }
        }

        $lines[] = '';
        $lines[] = 'Veículo em excelente estado de conservação, disponível para inspeção prévia.';
        $lines[] = 'Para mais informações ou para agendar uma visita, entre em contacto connosco!';

        return implode("\n", $lines);
    }

    private function calculateSaleMetrics(V3Vehicle $vehicle, float $salePrice, string $vatType): array
    {
        $expenses = $vehicle->expenses()
            ->whereNull('source_type')
            ->where('movement_type', 'expense')
            ->where('category', '!=', 'vehicle_purchase')
            ->get();

        return SaleCalculator::compute(
            $salePrice,
            $vatType,
            (float) ($vehicle->purchase_price ?? 0),
            (float) ($vehicle->purchase_vat_paid ?? 0),
            $expenses
        );
    }

    // ═══════════════════════════════════════════════════
    // LEGALIZATION TAB
    // ═══════════════════════════════════════════════════

    /** Create a legalization linked to this V3 vehicle */
    public function createLegalization(Request $request, $id)
    {
        $vehicle = V3Vehicle::findOrFail($id);

        if ($vehicle->legalization) {
            return back()->with('return_tab', 'legalization');
        }

        Legalization::create([
            'v3_vehicle_id' => $vehicle->id,
            'marca'         => $vehicle->brand ?? '',
            'modelo'        => trim(($vehicle->model ?? '') . ' ' . ($vehicle->sub_model ?? '')),
            'combustivel'   => $vehicle->fuel ?? 'Gasolina',
            'matricula'     => $vehicle->registration,
            'steps_completed' => [],
        ]);

        return back()->with('success', 'Processo de legalização criado.')->with('return_tab', 'legalization');
    }

    /** Update legalization fields (matricula, num_homologacao, notas) */
    public function saveLegalization(Request $request, $id)
    {
        $vehicle       = V3Vehicle::findOrFail($id);
        $legalization  = Legalization::where('v3_vehicle_id', $vehicle->id)->firstOrFail();

        $validated = $request->validate([
            'matricula'       => 'nullable|string|max:20',
            'num_homologacao' => 'nullable|string|max:100',
            'notas'           => 'nullable|string',
        ]);

        $legalization->update($validated);

        return back()->with('success', 'Dados de legalização guardados.')->with('return_tab', 'legalization');
    }

    /** Toggle a legalization step */
    public function toggleLegalizationStep(Request $request, $id)
    {
        $vehicle      = V3Vehicle::findOrFail($id);
        $legalization = Legalization::where('v3_vehicle_id', $vehicle->id)->firstOrFail();

        $request->validate(['step' => 'required|integer|min:1|max:8']);

        $step      = (int) $request->step;
        $completed = $legalization->steps_completed ?? [];
        $wasDone   = in_array($step, $completed);

        if (!$wasDone && $step > 1 && !in_array($step - 1, $completed)) {
            return response()->json(['error' => 'Conclui o passo ' . ($step - 1) . ' primeiro.'], 422);
        }

        if ($wasDone) {
            $completed = array_values(array_filter($completed, fn ($s) => $s !== $step));
        } else {
            $completed[] = $step;
            sort($completed);
        }

        $legalization->update(['steps_completed' => $completed]);

        return response()->json([
            'completed' => $completed,
            'progress'  => $legalization->progressPercent(),
        ]);
    }

    /** Upload a document to the linked legalization */
    public function uploadLegalizationDocument(Request $request, $id)
    {
        $vehicle      = V3Vehicle::findOrFail($id);
        $legalization = Legalization::where('v3_vehicle_id', $vehicle->id)->firstOrFail();

        $request->validate([
            'tipo'     => 'required|string|in:' . implode(',', array_keys(Legalization::DOCUMENTOS)),
            'ficheiro' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ]);

        $file = $request->file('ficheiro');
        $path = $file->store("legalizations/{$legalization->id}");

        $existing = $legalization->documents()->where('tipo', $request->tipo)->first();
        if ($existing) {
            Storage::delete($existing->caminho);
            $existing->update([
                'nome_original' => $file->getClientOriginalName(),
                'caminho'       => $path,
            ]);
        } else {
            LegalizationDocument::create([
                'legalization_id' => $legalization->id,
                'tipo'            => $request->tipo,
                'nome_original'   => $file->getClientOriginalName(),
                'caminho'         => $path,
            ]);
        }

        return back()->with('success', 'Documento carregado.')->with('return_tab', 'legalization');
    }

    /** Download a legalization document */
    public function downloadLegalizationDocument($vehicleId, $documentId)
    {
        $vehicle      = V3Vehicle::findOrFail($vehicleId);
        $legalization = Legalization::where('v3_vehicle_id', $vehicle->id)->firstOrFail();
        $document     = LegalizationDocument::where('legalization_id', $legalization->id)->findOrFail($documentId);

        return Storage::download($document->caminho, $document->nome_original);
    }

    /** Delete a legalization document */
    public function deleteLegalizationDocument($vehicleId, $documentId)
    {
        $vehicle      = V3Vehicle::findOrFail($vehicleId);
        $legalization = Legalization::where('v3_vehicle_id', $vehicle->id)->firstOrFail();
        $document     = LegalizationDocument::where('legalization_id', $legalization->id)->findOrFail($documentId);

        Storage::delete($document->caminho);
        $document->delete();

        return back()->with('success', 'Documento removido.')->with('return_tab', 'legalization');
    }
}
