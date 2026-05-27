<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\VehicleInspection;
use App\Models\VehicleInspectionEntry;
use App\Models\VehicleInspectionMedia;
use App\Services\VehicleInspectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VehicleInspectionController extends Controller
{
    public function __construct(private readonly VehicleInspectionService $inspectionService)
    {
    }

    public function index(Request $request)
    {
        $this->inspectionService->ensureDefaultTemplate();

        $query = VehicleInspection::with(['vehicle', 'template'])
            ->withCount('entries');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('registration', 'like', "%{$search}%")
                  ->orWhere('vin', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('fuel')) {
            $query->where('fuel', $request->fuel);
        }

        $inspections = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total' => VehicleInspection::count(),
            'draft' => VehicleInspection::where('status', 'draft')->count(),
            'completed' => VehicleInspection::where('status', 'completed')->count(),
            'converted' => VehicleInspection::where('status', 'converted')->count(),
        ];

        return view('admin.v3.inspections.index', compact('inspections', 'stats'));
    }

    public function create()
    {
        $template = $this->inspectionService->ensureDefaultTemplate();
        $brands = Brand::with(['models' => fn ($q) => $q->with('submodels')->orderBy('name')])
            ->orderBy('name')
            ->get();

        $inspection = new VehicleInspection([
            'status' => 'draft',
            'vehicle_inspection_template_id' => $template->id,
        ]);

        return view('admin.v3.inspections.create', compact('inspection', 'brands', 'template'));
    }

    public function store(Request $request)
    {
        $template = $this->inspectionService->ensureDefaultTemplate();

        $validated = $request->validate([
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'sub_model' => 'nullable|string|max:150',
            'version' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1900|max:' . (now()->year + 2),
            'kilometers' => 'nullable|integer|min:0',
            'vin' => 'nullable|string|max:32',
            'registration' => 'nullable|string|max:20',
            'color' => 'nullable|string|max:50',
            'fuel' => 'nullable|in:Diesel,Gasolina,Híbrido,Elétrico',
            'power' => 'nullable|integer|min:0',
            'transmission' => 'nullable|in:Manual,Automática',
            'traction' => 'nullable|in:FWD,RWD,AWD/4x4',
            'notes' => 'nullable|string',
        ]);

        $inspection = VehicleInspection::create(array_merge($validated, [
            'status' => 'draft',
            'vehicle_inspection_template_id' => $template->id,
        ]));

        $this->ensureEntries($inspection, $template);
        $this->inspectionService->recalculate($inspection);

        return redirect()
            ->route('admin.v3.inspections.edit', $inspection)
            ->with('success', 'Inspeção criada. Complete a checklist e anexe media.');
    }

    public function edit(VehicleInspection $inspection)
    {
        $template = $this->inspectionService->ensureDefaultTemplate();
        $inspection->load([
            'template.categories.items',
            'entries.item',
            'entries.media',
            'vehicle.photos',
            'vehicle.documents',
        ]);

        $this->ensureEntries($inspection, $template);
        $inspection->load(['entries.item', 'entries.media']);

        $brands = Brand::with(['models' => fn ($q) => $q->with('submodels')->orderBy('name')])
            ->orderBy('name')
            ->get();

        $summary = $this->inspectionService->calculateSummary($inspection);

        $entriesByItem    = $inspection->entries->keyBy('vehicle_inspection_item_id');
        $criticalEntries  = $inspection->entries->filter(fn ($e) => $e->status === 'problema' && ($e->item?->is_critical ?? false));
        $problemEntries   = $inspection->entries->filter(fn ($e) => $e->status === 'problema');

        return view('admin.v3.inspections.edit', compact(
            'inspection', 'brands', 'template', 'summary',
            'entriesByItem', 'criticalEntries', 'problemEntries'
        ));
    }

    public function update(Request $request, VehicleInspection $inspection)
    {
        $template = $this->inspectionService->ensureDefaultTemplate();
        $this->ensureEntries($inspection, $template);

        $validated = $request->validate([
            'status' => 'nullable|in:draft,in_progress,completed,converted',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'sub_model' => 'nullable|string|max:150',
            'version' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1900|max:' . (now()->year + 2),
            'kilometers' => 'nullable|integer|min:0',
            'vin' => 'nullable|string|max:32',
            'registration' => 'nullable|string|max:20',
            'color' => 'nullable|string|max:50',
            'fuel' => 'nullable|in:Diesel,Gasolina,Híbrido,Elétrico',
            'power' => 'nullable|integer|min:0',
            'transmission' => 'nullable|in:Manual,Automática',
            'traction' => 'nullable|in:FWD,RWD,AWD/4x4',
            'notes' => 'nullable|string',
            'entries' => 'nullable|array',
            'entries.*.status' => 'nullable|in:ok,atencao,problema,nao_verificado',
            'entries.*.priority' => 'nullable|in:baixa,media,alta',
            'entries.*.notes' => 'nullable|string',
        ]);

        $inspection->fill(collect($validated)->except('entries')->all());
        $inspection->status = $validated['status'] ?? $inspection->status;

        foreach ($request->input('entries', []) as $entryId => $entryData) {
            $entry = $inspection->entries()->whereKey($entryId)->first();
            if (! $entry) {
                continue;
            }

            $entry->update([
                'status' => $entryData['status'] ?? $entry->status,
                'priority' => $entryData['priority'] ?? $entry->priority,
                'notes' => $entryData['notes'] ?? null,
            ]);
        }

        if ($inspection->status === 'completed' && ! $inspection->completed_at) {
            $inspection->completed_at = now();
        }

        $inspection->save();
        $inspection = $this->inspectionService->recalculate($inspection);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Inspeção guardada.',
                'summary' => $this->inspectionService->calculateSummary($inspection),
            ]);
        }

        return back()->with('success', 'Inspeção guardada.');
    }

    public function uploadMedia(Request $request, VehicleInspection $inspection, VehicleInspectionEntry $entry)
    {
        abort_unless($entry->vehicle_inspection_id === $inspection->id, 404);

        $validated = $request->validate([
            'files' => 'required|array|min:1',
            'files.*' => 'file|max:51200',
            'descriptions' => 'nullable|array',
            'descriptions.*' => 'nullable|string|max:255',
        ]);

        $nextOrder = (int) ($entry->media()->max('sort_order') ?? 0);

        foreach ($request->file('files', []) as $index => $file) {
            $mime = (string) $file->getMimeType();
            $type = str_starts_with($mime, 'video/') ? 'video' : 'image';
            $path = $file->store("inspections/{$inspection->id}/entries/{$entry->id}", 'public');

            VehicleInspectionMedia::create([
                'vehicle_inspection_entry_id' => $entry->id,
                'type' => $type,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'description' => $validated['descriptions'][$index] ?? null,
                'sort_order' => ++$nextOrder,
            ]);
        }

        return back()->with('success', 'Media adicionada.');
    }

    public function reorderMedia(Request $request, VehicleInspection $inspection, VehicleInspectionEntry $entry)
    {
        abort_unless($entry->vehicle_inspection_id === $inspection->id, 404);

        $validated = $request->validate([
            'media_ids' => 'required|array|min:1',
            'media_ids.*' => 'integer|exists:vehicle_inspection_media,id',
        ]);

        foreach ($validated['media_ids'] as $index => $mediaId) {
            $media = $entry->media()->whereKey($mediaId)->first();
            if ($media) {
                $media->update(['sort_order' => $index + 1]);
            }
        }

        return response()->json(['success' => true]);
    }

    public function destroyMedia(Request $request, VehicleInspection $inspection, VehicleInspectionEntry $entry, VehicleInspectionMedia $media)
    {
        abort_unless($entry->vehicle_inspection_id === $inspection->id && $media->vehicle_inspection_entry_id === $entry->id, 404);

        Storage::disk('public')->delete($media->path);
        $media->delete();

        return back()->with('success', 'Media removida.');
    }

    public function convert(Request $request, VehicleInspection $inspection)
    {
        if ($inspection->v3_vehicle_id) {
            return redirect()->route('admin.v3.vehicles.edit', $inspection->v3_vehicle_id);
        }

        $vehicle = $this->inspectionService->createV3Vehicle($inspection);

        return redirect()
            ->route('admin.v3.vehicles.edit', $vehicle->id)
            ->with('success', 'Veículo V3 criado automaticamente a partir da inspeção.');
    }

    private function ensureEntries(VehicleInspection $inspection, $template): void
    {
        $inspection->loadMissing('entries');

        foreach ($template->categories as $category) {
            foreach ($category->items as $item) {
                $inspection->entries()->firstOrCreate(
                    ['vehicle_inspection_item_id' => $item->id],
                    [
                        'status' => 'nao_verificado',
                        'priority' => 'baixa',
                    ]
                );
            }
        }
    }
}
