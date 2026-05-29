<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\VehicleInspectionTemplate;
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

        // Stats count only original inspections (not revisions)
        $stats = [
            'total' => VehicleInspection::whereNull('parent_inspection_id')->count(),
            'draft' => VehicleInspection::whereNull('parent_inspection_id')->where('status', 'draft')->count(),
            'completed' => VehicleInspection::whereNull('parent_inspection_id')->where('status', 'completed')->count(),
            'converted' => VehicleInspection::whereNull('parent_inspection_id')->where('status', 'converted')->count(),
        ];

        return view('admin.v3.inspections.index', compact('inspections', 'stats'));
    }

    public function create()
    {
        $template = $this->inspectionService->ensureDefaultTemplate();
        $templates = VehicleInspectionTemplate::orderBy('name')->get();
        $brands = Brand::with(['models' => fn ($q) => $q->with('submodels')->orderBy('name')])
            ->orderBy('name')
            ->get();

        $inspection = new VehicleInspection([
            'status' => 'draft',
            'vehicle_inspection_template_id' => $template->id,
        ]);

        return view('admin.v3.inspections.create', compact('inspection', 'brands', 'template', 'templates'));
    }

    public function store(Request $request)
    {


        // se não existir template_id ou for inválido, usar o default   
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
            'template_id' => 'nullable|exists:vehicle_inspection_templates,id',
        ]);

        if(!empty($validated['template_id'])) {
            $template = VehicleInspectionTemplate::find($validated['template_id']);

            
        }else{ 
        $template = $this->inspectionService->ensureDefaultTemplate();
        }

        $inspection = VehicleInspection::create(array_merge($validated, [
            'status' => 'draft',
            'vehicle_inspection_template_id' =>  $template->id,
        ]));
        $this->ensureEntries($inspection, $template);
        $this->inspectionService->recalculate($inspection);

        return redirect()
            ->route('admin.v3.inspections.edit', $inspection)
            ->with('success', 'Inspeção criada. Complete a checklist e anexe media.');
    }

    public function report(VehicleInspection $inspection)
    {
        $inspection->load([
            'template.categories' => fn ($q) => $q->orderBy('sort_order'),
            'template.categories.items' => fn ($q) => $q->orderBy('sort_order'),
            'entries.item.category',
            'entries.media' => fn ($q) => $q->orderBy('sort_order'),
            'generalMedia',
            'parent.entries.item.category',
        ]);

        $entriesByItem = $inspection->entries->keyBy('vehicle_inspection_item_id');

        $categoriesWithEntries = $inspection->template->categories->map(function ($category) use ($entriesByItem) {
            $verifiedItems = $category->items->map(function ($item) use ($entriesByItem) {
                $entry = $entriesByItem->get($item->id);
                return ($entry && $entry->status !== 'nao_verificado')
                    ? ['item' => $item, 'entry' => $entry]
                    : null;
            })->filter()->values();

            return ['category' => $category, 'verifiedItems' => $verifiedItems];
        })->filter(fn ($c) => $c['verifiedItems']->count() > 0)->values();

        $problemEntries = $inspection->entries
            ->filter(fn ($e) => $e->status === 'problema')
            ->sortByDesc(fn ($e) => ['alta' => 3, 'media' => 2, 'baixa' => 1][$e->priority] ?? 0)
            ->values();

        $summary = $this->inspectionService->calculateSummary($inspection);

        // Compute entry-level diff against parent inspection
        $changedEntries = collect();
        if ($inspection->parent) {
            $parentEntriesByItem = $inspection->parent->entries->keyBy('vehicle_inspection_item_id');
            $changedEntries = $inspection->entries
                ->filter(function ($entry) use ($parentEntriesByItem) {
                    $p = $parentEntriesByItem->get($entry->vehicle_inspection_item_id);
                    if (! $p) {
                        return false;
                    }
                    return $p->status !== $entry->status ||
                        ($entry->status !== 'nao_verificado' && $p->priority !== $entry->priority);
                })
                ->map(function ($entry) use ($parentEntriesByItem) {
                    $p = $parentEntriesByItem->get($entry->vehicle_inspection_item_id);
                    return [
                        'entry'        => $entry,
                        'item'         => $entry->item,
                        'old_status'   => $p->status,
                        'new_status'   => $entry->status,
                        'old_priority' => $p->priority,
                        'new_priority' => $entry->priority,
                    ];
                })
                ->values();
        }

        return view('admin.v3.inspections.report', compact(
            'inspection', 'categoriesWithEntries', 'problemEntries', 'summary', 'changedEntries'
        ));
    }

    public function downloadPdf(VehicleInspection $inspection)
    {
        $inspection->load([
            'template.categories' => fn ($q) => $q->orderBy('sort_order'),
            'template.categories.items' => fn ($q) => $q->orderBy('sort_order'),
            'entries.item.category',
            'entries.media' => fn ($q) => $q->orderBy('sort_order'),
            'generalMedia',
            'parent.entries.item.category',
        ]);

        $entriesByItem = $inspection->entries->keyBy('vehicle_inspection_item_id');

        $categoriesWithEntries = $inspection->template->categories->map(function ($category) use ($entriesByItem) {
            $verifiedItems = $category->items->map(function ($item) use ($entriesByItem) {
                $entry = $entriesByItem->get($item->id);
                return ($entry && $entry->status !== 'nao_verificado')
                    ? ['item' => $item, 'entry' => $entry]
                    : null;
            })->filter()->values();

            return ['category' => $category, 'verifiedItems' => $verifiedItems];
        })->filter(fn ($c) => $c['verifiedItems']->count() > 0)->values();

        $problemEntries = $inspection->entries
            ->filter(fn ($e) => $e->status === 'problema')
            ->sortByDesc(fn ($e) => ['alta' => 3, 'media' => 2, 'baixa' => 1][$e->priority] ?? 0)
            ->values();

        $summary = $this->inspectionService->calculateSummary($inspection);

        // Compute entry-level diff against parent inspection
        $changedEntries = collect();
        if ($inspection->parent) {
            $parentEntriesByItem = $inspection->parent->entries->keyBy('vehicle_inspection_item_id');
            $changedEntries = $inspection->entries
                ->filter(function ($entry) use ($parentEntriesByItem) {
                    $p = $parentEntriesByItem->get($entry->vehicle_inspection_item_id);
                    if (! $p) {
                        return false;
                    }
                    return $p->status !== $entry->status ||
                        ($entry->status !== 'nao_verificado' && $p->priority !== $entry->priority);
                })
                ->map(function ($entry) use ($parentEntriesByItem) {
                    $p = $parentEntriesByItem->get($entry->vehicle_inspection_item_id);
                    return [
                        'entry'        => $entry,
                        'item'         => $entry->item,
                        'old_status'   => $p->status,
                        'new_status'   => $entry->status,
                        'old_priority' => $p->priority,
                        'new_priority' => $entry->priority,
                    ];
                })
                ->values();
        }

        $html = view('admin.v3.inspections.report-pdf', compact(
            'inspection', 'categoriesWithEntries', 'problemEntries', 'summary', 'changedEntries'
        ))->render();

        $pdf = app('dompdf.wrapper')->loadHTML($html);
        $filename = 'relatorio-inspecao-' . $inspection->id . '-' . now()->format('Y-m-d-H-i') . '.pdf';

        return $pdf->download($filename);
    }

    public function edit(VehicleInspection $inspection)
    {
        // Completed / converted inspections are read-only — redirect to report
        if (in_array($inspection->status, ['completed', 'converted'])) {
            return redirect()
                ->route('admin.v3.inspections.report', $inspection)
                ->with('info', 'Esta inspecção está concluída e não pode ser editada. Crie uma nova revisão se necessário.');
        }

        $template = $this->inspectionService->ensureDefaultTemplate();
        $inspection->load([
            'template.categories.items',
            'entries.item',
            'entries.media',
            'generalMedia',
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
        $generalMedia     = $inspection->generalMedia;

        return view('admin.v3.inspections.edit', compact(
            'inspection', 'brands', 'template', 'summary',
            'entriesByItem', 'criticalEntries', 'problemEntries', 'generalMedia'
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

    public function duplicate(VehicleInspection $inspection)
    {
        $template = $this->inspectionService->ensureDefaultTemplate();

        $newInspection = VehicleInspection::create([
            'vehicle_inspection_template_id' => $inspection->vehicle_inspection_template_id,
            'parent_inspection_id' => $inspection->id,
            'status'       => 'draft',
            'brand'        => $inspection->brand,
            'model'        => $inspection->model,
            'sub_model'    => $inspection->sub_model,
            'version'      => $inspection->version,
            'year'         => $inspection->year,
            'kilometers'   => $inspection->kilometers,
            'vin'          => $inspection->vin,
            'registration' => $inspection->registration,
            'color'        => $inspection->color,
            'fuel'         => $inspection->fuel,
            'power'        => $inspection->power,
            'transmission' => $inspection->transmission,
            'traction'     => $inspection->traction,
        ]);

        $this->ensureEntries($newInspection, $template);
        $newInspection->load(['entries']);

        $originalEntriesByItem = $inspection->load('entries')->entries->keyBy('vehicle_inspection_item_id');
        foreach ($newInspection->entries as $entry) {
            $original = $originalEntriesByItem->get($entry->vehicle_inspection_item_id);
            if ($original) {
                $entry->update([
                    'status'   => $original->status,
                    'priority' => $original->priority,
                    'notes'    => $original->notes,
                ]);
            }
        }

        $this->inspectionService->recalculate($newInspection);

        return redirect()
            ->route('admin.v3.inspections.edit', $newInspection)
            ->with('success', 'Nova revisão criada a partir da Inspecção #' . $inspection->id . '. Actualize o que for necessário e conclua.');
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

    public function destroy(VehicleInspection $inspection)
    {
        // Only allow deletion of inspections that were never completed/converted,
        // or any revision (has parent_inspection_id)
        $canDelete = $inspection->parent_inspection_id !== null || 
                     ! in_array($inspection->status, ['completed', 'converted']);

        if (! $canDelete) {
            return back()->with('error', 'Não é possível apagar uma inspeção concluída ou convertida. Crie uma nova revisão em vez de a apagar.');
        }

        // Delete all related media
        $inspection->entries()->each(function ($entry) {
            $entry->media()->delete();
        });
        $inspection->generalMedia()->delete();
        
        // Delete all entries
        $inspection->entries()->delete();
        
        // Delete the inspection itself
        $inspection->delete();

        return redirect()
            ->route('admin.v3.inspections.index')
            ->with('success', 'Inspeção apagada com sucesso.');
    }

    public function uploadGeneralMedia(Request $request, VehicleInspection $inspection)
    {
        $request->validate([
            'files'   => 'required|array|min:1',
            'files.*' => 'file|max:51200',
        ]);

        $nextOrder = (int) ($inspection->generalMedia()->max('sort_order') ?? 0);

        foreach ($request->file('files', []) as $file) {
            $mime = (string) $file->getMimeType();
            $type = str_starts_with($mime, 'video/') ? 'video' : 'image';
            $path = $file->store("inspections/{$inspection->id}/general", 'public');

            VehicleInspectionMedia::create([
                'vehicle_inspection_id'       => $inspection->id,
                'vehicle_inspection_entry_id' => null,
                'type'          => $type,
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
                'sort_order'    => ++$nextOrder,
            ]);
        }

        return back()->with('success', 'Fotos adicionadas.');
    }

    public function destroyGeneralMedia(Request $request, VehicleInspection $inspection, VehicleInspectionMedia $media)
    {
        abort_unless(
            $media->vehicle_inspection_id === $inspection->id && is_null($media->vehicle_inspection_entry_id),
            404
        );

        Storage::disk('public')->delete($media->path);
        $media->delete();

        return back()->with('success', 'Foto removida.');
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
