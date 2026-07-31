<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Client;
use App\Models\Legalization;
use App\Models\LegalizationDocument;
use App\Models\V3Vehicle;
use App\Services\Modelo9PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LegalizationController extends Controller
{
    // ---------------------------------------------------------------
    // Index — lista de legalizações
    // ---------------------------------------------------------------
    public function index()
    {
        $legalizations = Legalization::with(['client', 'documents'])
            ->latest()
            ->get();

        $stats = [
            'total'      => $legalizations->count(),
            'concluidas' => $legalizations->filter(fn ($l) => $l->progressPercent() === 100)->count(),
            'em_curso'   => $legalizations->filter(fn ($l) => $l->progressPercent() > 0 && $l->progressPercent() < 100)->count(),
            'novas'      => $legalizations->filter(fn ($l) => $l->progressPercent() === 0)->count(),
        ];

        return view('admin.v2.legalizations.index', compact('legalizations', 'stats'));
    }

    // ---------------------------------------------------------------
    // Create — formulário de nova legalização
    // ---------------------------------------------------------------
    public function create()
    {
        $clients  = Client::orderBy('name')->get();
        $brands   = Brand::orderBy('name')->get();
        $vehicles = V3Vehicle::select('id', 'reference', 'brand', 'model', 'fuel', 'registration')
            ->whereNotIn('status', ['vendido'])
            ->orderBy('reference')->get();

        return view('admin.v2.legalizations.create', compact('clients', 'brands', 'vehicles'));
    }

    // ---------------------------------------------------------------
    // Store — gravar nova legalização
    // ---------------------------------------------------------------
    public function store(Request $request)
    {
        $validated = $request->validate([
            'v3_vehicle_id'      => 'nullable|exists:v3_vehicles,id',
            'client_id'          => 'nullable|exists:clients,id',
            'marca'              => 'required_without:v3_vehicle_id|nullable|string|max:100',
            'modelo'             => 'required_without:v3_vehicle_id|nullable|string|max:100',
            'combustivel'        => 'required_without:v3_vehicle_id|nullable|string|max:50',
            'matricula'          => 'nullable|string|max:20',
            'num_homologacao'    => 'nullable|string|max:100',
            'num_processo_imt'   => 'nullable|string|max:100',
            'notas'              => 'nullable|string',
            'regime_especial_isv' => 'nullable|boolean',
        ]);

        $validated['regime_especial_isv'] = $validated['regime_especial_isv'] ?? false;

        // If a V3 vehicle is selected, fill fields from it
        if (!empty($validated['v3_vehicle_id'])) {
            $vehicle = V3Vehicle::findOrFail($validated['v3_vehicle_id']);
            $validated['marca']       = $vehicle->brand;
            $validated['modelo']      = $vehicle->model;
            $validated['combustivel'] = $vehicle->fuel ?? 'Gasolina';
            if ($vehicle->registration) {
                $validated['matricula'] = $validated['matricula'] ?? $vehicle->registration;
            }
        }

        // Ensure required fields are filled
        $validated['marca']       = $validated['marca'] ?? '';
        $validated['modelo']      = $validated['modelo'] ?? '';
        $validated['combustivel'] = $validated['combustivel'] ?? 'Gasolina';

        $legalization = Legalization::create($validated);

        return redirect()->route('admin.legalizations.show', $legalization)
            ->with('success', 'Legalização criada com sucesso.');
    }

    // ---------------------------------------------------------------
    // Show — detalhe com passos e documentos
    // ---------------------------------------------------------------
    public function show(Legalization $legalization)
    {
        $legalization->load('client', 'documents');

        $passos    = Legalization::PASSOS;
        $documentos = $legalization->allDocumentos();

        return view('admin.v2.legalizations.show', compact('legalization', 'passos', 'documentos'));
    }

    // ---------------------------------------------------------------
    // Toggle step — marcar/desmarcar passo como concluído (AJAX)
    // ---------------------------------------------------------------
    public function toggleStep(Request $request, Legalization $legalization)
    {
        $request->validate(['step' => 'required|integer|min:1|max:7']);

        $step      = (int) $request->step;
        $completed = $legalization->steps_completed ?? [];
        $wasCompleted = in_array($step, $completed);

        // Bloqueia conclusão se o passo anterior não está concluído
        if (!$wasCompleted && $step > 1 && !in_array($step - 1, $completed)) {
            return response()->json([
                'error' => 'Conclui o passo ' . ($step - 1) . ' antes de avançar.',
            ], 422);
        }

        if ($wasCompleted) {
            $completed = array_values(array_filter($completed, fn ($s) => $s !== $step));
        } else {
            $completed[] = $step;
            sort($completed);
        }

        $legalization->update(['steps_completed' => $completed]);

        $taskCreated = false;

        // Ao concluir o passo 5 cria automaticamente a tarefa de entrega do Modelo 9 IMT
        if ($step === 5 && !$wasCompleted) {
            $legalization->load('client');

            $clienteNome = $legalization->client?->name ?? 'Cliente sem nome';
            $matricula   = $legalization->matricula ?: 'matrícula não registada';
            $veiculo     = trim($legalization->marca . ' ' . $legalization->modelo);

            \App\Models\Task::create([
                'title'         => 'Entregar Modelo 9 IMT — ' . $veiculo,
                'description'   => "Entregar o Modelo 9 no IMT referente ao processo de legalização #{$legalization->id} do veículo {$veiculo} ({$matricula}), do cliente {$clienteNome}.",
                'due_date'      => now()->addDays(30)->toDateString(),
                'reminder_date' => now()->addDays(15)->toDateString(),
                'status'        => 'pendente',
            ]);

            $taskCreated = true;
        }

        return response()->json([
            'completed'    => $completed,
            'progress'     => $legalization->progressPercent(),
            'task_created' => $taskCreated,
        ]);
    }

    // ---------------------------------------------------------------
    // Upload de documento
    // ---------------------------------------------------------------
    public function uploadDocument(Request $request, Legalization $legalization)
    {
        $validated = $request->validate([
            'tipo'     => 'required|string|in:' . implode(',', array_keys(array_merge(Legalization::DOCUMENTOS, Legalization::DOCUMENTOS_REGIME_ESPECIAL))),
            'ficheiro' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ]);

        $file = $request->file('ficheiro');
        $path = $file->store("legalizations/{$legalization->id}", 'local');

        // Substitui se já existir um doc do mesmo tipo
        $existing = $legalization->documents()->where('tipo', $request->tipo)->first();
        if ($existing) {
            Storage::disk('local')->delete($existing->caminho);
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

        return back()->with('success', 'Documento carregado com sucesso.');
    }

    // ---------------------------------------------------------------
    // Download de documento
    // ---------------------------------------------------------------
    public function downloadDocument(Legalization $legalization, LegalizationDocument $document)
    {
        abort_if($document->legalization_id !== $legalization->id, 403);

        return Storage::disk('local')->download($document->caminho, $document->nome_original);
    }

    // ---------------------------------------------------------------
    // Apagar documento
    // ---------------------------------------------------------------
    public function deleteDocument(Legalization $legalization, LegalizationDocument $document)
    {
        abort_if($document->legalization_id !== $legalization->id, 403);

        Storage::disk('local')->delete($document->caminho);
        $document->delete();

        return back()->with('success', 'Documento removido.');
    }

    // ---------------------------------------------------------------
    // Upload de fatura de serviço
    // ---------------------------------------------------------------
    public function uploadInvoice(Request $request, Legalization $legalization)
    {
        $request->validate([
            'invoice' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ]);

        // Apaga o anterior se existir
        if ($legalization->invoice_path) {
            Storage::disk('local')->delete($legalization->invoice_path);
        }

        $path = $request->file('invoice')->store("legalizations/{$legalization->id}", 'local');
        $legalization->update(['invoice_path' => $path]);

        return back()->with('success', 'Fatura carregada com sucesso.');
    }

    // ---------------------------------------------------------------
    // Download de fatura de serviço
    // ---------------------------------------------------------------
    public function downloadInvoice(Legalization $legalization)
    {
        abort_unless($legalization->invoice_path && Storage::disk('local')->exists($legalization->invoice_path), 404);

        return Storage::disk('local')->download(
            $legalization->invoice_path,
            'fatura_' . $legalization->id . '.' . pathinfo($legalization->invoice_path, PATHINFO_EXTENSION)
        );
    }

    // ---------------------------------------------------------------
    // Apagar fatura de serviço
    // ---------------------------------------------------------------
    public function deleteInvoice(Legalization $legalization)
    {
        if ($legalization->invoice_path) {
            Storage::disk('local')->delete($legalization->invoice_path);
            $legalization->update(['invoice_path' => null]);
        }

        return back()->with('success', 'Fatura removida.');
    }

    // ---------------------------------------------------------------
    // Edit
    // ---------------------------------------------------------------
    public function edit(Legalization $legalization)
    {
        $clients  = Client::orderBy('name')->get();
        $brands   = Brand::orderBy('name')->get();
        $vehicles = V3Vehicle::select('id', 'reference', 'brand', 'model', 'fuel', 'registration')
            ->orderBy('reference')->get();

        return view('admin.v2.legalizations.edit', compact('legalization', 'clients', 'brands', 'vehicles'));
    }

    // ---------------------------------------------------------------
    // Update
    // ---------------------------------------------------------------
    public function update(Request $request, Legalization $legalization)
    {
        $validated = $request->validate([
            'v3_vehicle_id'      => 'nullable|exists:v3_vehicles,id',
            'client_id'          => 'nullable|exists:clients,id',
            'marca'              => 'nullable|string|max:100',
            'modelo'             => 'nullable|string|max:100',
            'combustivel'        => 'nullable|string|max:50',
            'matricula'          => 'nullable|string|max:20',
            'num_homologacao'    => 'nullable|string|max:100',
            'num_processo_imt'   => 'nullable|string|max:100',
            'notas'              => 'nullable|string',
            'regime_especial_isv' => 'nullable|boolean',
            'modelo9'            => 'nullable|array',
        ]);

        $validated['regime_especial_isv'] = $validated['regime_especial_isv'] ?? false;

        if (!empty($validated['v3_vehicle_id'])) {
            $vehicle = V3Vehicle::findOrFail($validated['v3_vehicle_id']);
            $validated['marca']       = $vehicle->brand;
            $validated['modelo']      = $vehicle->model;
            $validated['combustivel'] = $vehicle->fuel ?? 'Gasolina';
            // clear legacy vehicle_id
            $validated['vehicle_id']  = null;
        }

        $validated['modelo9_dados'] = $this->buildModelo9Dados($request);
        unset($validated['modelo9']);

        $legalization->update($validated);

        return redirect()->route('admin.legalizations.show', $legalization)
            ->with('success', 'Legalização actualizada.');
    }

    // ---------------------------------------------------------------
    // Gerar Modelo 9 IMT pré-preenchido (sem alterar dados guardados)
    // ---------------------------------------------------------------
    public function generateModelo9(Legalization $legalization)
    {
        return $this->respondWithModelo9Pdf($legalization);
    }

    // ---------------------------------------------------------------
    // Guardar os dados extra do Modelo 9 (a partir da modal) e gerar o PDF
    // ---------------------------------------------------------------
    public function saveAndGenerateModelo9(Request $request, Legalization $legalization)
    {
        $request->validate(['modelo9' => 'nullable|array']);

        $legalization->update(['modelo9_dados' => $this->buildModelo9Dados($request)]);

        return $this->respondWithModelo9Pdf($legalization);
    }

    private function respondWithModelo9Pdf(Legalization $legalization)
    {
        $pdfContent = (new Modelo9PdfService())->generate($legalization);
        $filename   = 'modelo9_imt_' . $legalization->id . '.pdf';

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    // ---------------------------------------------------------------
    // Constrói o array modelo9_dados a partir do input 'modelo9[...]' do request
    // ---------------------------------------------------------------
    private function buildModelo9Dados(Request $request): array
    {
        $modelo9Input = $request->input('modelo9', []);
        $dados = [];
        foreach (Modelo9PdfService::CAMPOS_EXTRA_TEXTO as $key) {
            $dados[$key] = $modelo9Input[$key] ?? null;
        }
        foreach (Modelo9PdfService::CAMPOS_EXTRA_BOOLEAN as $key) {
            $dados[$key] = !empty($modelo9Input[$key]);
        }
        return $dados;
    }

    // ---------------------------------------------------------------
    // Destroy
    // ---------------------------------------------------------------
    public function destroy(Legalization $legalization)
    {
        // Apaga todos os ficheiros associados
        foreach ($legalization->documents as $doc) {
            Storage::disk('local')->delete($doc->caminho);
        }
        $legalization->delete();

        return redirect()->route('admin.legalizations.index')
            ->with('success', 'Legalização eliminada.');
    }
}
