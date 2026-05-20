<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Client;
use App\Models\Legalization;
use App\Models\LegalizationDocument;
use App\Models\Vehicle;
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
        $vehicles = Vehicle::select('id', 'reference', 'brand', 'model', 'fuel', 'registration')
            ->orderBy('reference')->get();

        return view('admin.v2.legalizations.create', compact('clients', 'brands', 'vehicles'));
    }

    // ---------------------------------------------------------------
    // Store — gravar nova legalização
    // ---------------------------------------------------------------
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id'      => 'nullable|exists:vehicles,id',
            'client_id'       => 'nullable|exists:clients,id',
            'marca'           => 'required_without:vehicle_id|nullable|string|max:100',
            'modelo'          => 'required_without:vehicle_id|nullable|string|max:100',
            'combustivel'     => 'required_without:vehicle_id|nullable|string|max:50',
            'matricula'       => 'nullable|string|max:20',
            'num_homologacao' => 'nullable|string|max:100',
            'notas'           => 'nullable|string',
        ]);

        // If a vehicle is selected, fill fields from it
        if (!empty($validated['vehicle_id'])) {
            $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
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
        $documentos = Legalization::DOCUMENTOS;

        return view('admin.v2.legalizations.show', compact('legalization', 'passos', 'documentos'));
    }

    // ---------------------------------------------------------------
    // Toggle step — marcar/desmarcar passo como concluído (AJAX)
    // ---------------------------------------------------------------
    public function toggleStep(Request $request, Legalization $legalization)
    {
        $request->validate(['step' => 'required|integer|min:1|max:8']);

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
            'tipo'     => 'required|string|in:' . implode(',', array_keys(Legalization::DOCUMENTOS)),
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
    // Edit
    // ---------------------------------------------------------------
    public function edit(Legalization $legalization)
    {
        $clients  = Client::orderBy('name')->get();
        $brands   = Brand::orderBy('name')->get();
        $vehicles = Vehicle::select('id', 'reference', 'brand', 'model', 'fuel', 'registration')
            ->orderBy('reference')->get();

        return view('admin.v2.legalizations.edit', compact('legalization', 'clients', 'brands', 'vehicles'));
    }

    // ---------------------------------------------------------------
    // Update
    // ---------------------------------------------------------------
    public function update(Request $request, Legalization $legalization)
    {
        $validated = $request->validate([
            'vehicle_id'      => 'nullable|exists:vehicles,id',
            'client_id'       => 'nullable|exists:clients,id',
            'marca'           => 'nullable|string|max:100',
            'modelo'          => 'nullable|string|max:100',
            'combustivel'     => 'nullable|string|max:50',
            'matricula'       => 'nullable|string|max:20',
            'num_homologacao' => 'nullable|string|max:100',
            'notas'           => 'nullable|string',
        ]);

        if (!empty($validated['vehicle_id'])) {
            $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
            $validated['marca']       = $vehicle->brand;
            $validated['modelo']      = $vehicle->model;
            $validated['combustivel'] = $vehicle->fuel ?? 'Gasolina';
        }

        $legalization->update($validated);

        return redirect()->route('admin.legalizations.show', $legalization)
            ->with('success', 'Legalização actualizada.');
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
