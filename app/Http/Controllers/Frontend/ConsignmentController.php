<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\ConsignmentEvaluation;
use App\Models\Page;
use App\Http\Controllers\Frontend\PageController;

class ConsignmentController extends Controller
{
    public function evaluationForm()
    {
        return view('frontend.consignment-form');
    }

    public function submitEvaluation(Request $request)
    {
        $validated = $request->validate([
            'brand'             => 'required|string|max:100',
            'model'             => 'required|string|max:100',
            'version'           => 'nullable|string|max:200',
            'year'              => 'required|integer|min:1990|max:' . (date('Y') + 1),
            'kilometers'        => 'required|integer|min:0',
            'plate'             => 'required|string|max:20',
            'fuel'              => 'required|string|max:60',
            'gearbox'           => 'required|string|max:30',
            'power'             => 'nullable|integer|min:0',
            'displacement'      => 'nullable|integer|min:0',
            'color'             => 'nullable|string|max:60',
            'condition'         => 'required|string|max:30',
            'description'       => 'nullable|string|max:2000',
            'has_service_book'  => 'nullable',
            'has_2nd_key'       => 'nullable',
            'has_iuc'           => 'nullable',
            'has_inspection'    => 'nullable',
            'name'              => 'required|string|max:255',
            'phone'             => 'required|string|max:20',
            'email'             => 'required|email|max:255',
            'location'          => 'nullable|string|max:100',
            'price_expectation' => 'nullable|integer|min:0',
            'privacy_consent'   => 'required|accepted',
        ]);
        /* fotos processadas separadamente para não bloquear o envio */

        try {
            $ref         = 'CSG-' . strtoupper(substr(md5(microtime()), 0, 6));
            $photoMeta   = [];
            $storedPaths = [];

            $rawPhotos = $request->files->get('photos') ?? [];
            if (!is_array($rawPhotos)) {
                $rawPhotos = [$rawPhotos];
            }
            foreach ($rawPhotos as $photo) {
                try {
                    if (!$photo || !$photo->isValid()) {
                        continue;
                    }
                    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                    if (!in_array(strtolower($photo->getClientOriginalExtension()), $allowed)) {
                        continue;
                    }
                    $path = $photo->store('consignment-evaluations/' . $ref, 'public');
                    if ($path) {
                        $storedPaths[] = Storage::disk('public')->path($path);
                        $photoMeta[]   = $photo->getClientOriginalName() . ' (' . round($photo->getSize() / 1024) . ' KB)';
                    }
                } catch (\Throwable $e) {
                    \Log::warning('ConsignmentEvaluation photo skip: ' . $e->getMessage());
                }
            }

            $bool = function ($key) use ($request) {
                return $request->has($key) ? 'Sim' : 'Nao';
            };

            $sep  = str_repeat('-', 55);
            $desc = $validated['description'] ?? '';

            $body  = "PEDIDO DE AVALIACAO PARA CONSIGNACAO - {$ref}\n";
            $body .= "{$sep}\n\n";
            $body .= "VEICULO\n";
            $body .= "  Marca:          {$validated['brand']}\n";
            $body .= "  Modelo:         {$validated['model']}\n";
            $body .= "  Versao:         " . ($validated['version'] ?? '-') . "\n";
            $body .= "  Ano:            {$validated['year']}\n";
            $body .= "  Quilometros:    " . number_format((int) $validated['kilometers'], 0, ',', '.') . " km\n";
            $body .= "  Matricula:      {$validated['plate']}\n";
            $body .= "  Combustivel:    {$validated['fuel']}\n";
            $body .= "  Transmissao:    {$validated['gearbox']}\n";
            $body .= "  Potencia:       " . ($validated['power'] ? $validated['power'] . ' cv' : '-') . "\n";
            $body .= "  Cilindrada:     " . ($validated['displacement'] ? $validated['displacement'] . ' cc' : '-') . "\n";
            $body .= "  Cor:            " . ($validated['color'] ?? '-') . "\n\n";
            $body .= "ESTADO\n";
            $body .= "  Estado geral:   {$validated['condition']}\n";
            $body .= "  Livrete revs.:  " . $bool('has_service_book') . "\n";
            $body .= "  2a chave:       " . $bool('has_2nd_key') . "\n";
            $body .= "  IUC em dia:     " . $bool('has_iuc') . "\n";
            $body .= "  Inspecao:       " . $bool('has_inspection') . "\n";
            $body .= "  Descricao:      " . ($desc ?: '-') . "\n\n";
            $body .= "FOTOS\n";
            $body .= "  " . (count($photoMeta) > 0 ? implode("\n  ", $photoMeta) : 'Nenhuma foto enviada') . "\n\n";
            $body .= "CONTACTO\n";
            $body .= "  Nome:           {$validated['name']}\n";
            $body .= "  Telemovel:      {$validated['phone']}\n";
            $body .= "  E-mail:         {$validated['email']}\n";
            $body .= "  Localizacao:    " . ($validated['location'] ?? '-') . "\n";
            $body .= "  Valor esperado: " . ($validated['price_expectation'] ? number_format((int) $validated['price_expectation'], 0, ',', '.') . ' EUR' : '-') . "\n";

            $subject = "Avaliacao Consignacao - {$validated['brand']} {$validated['model']} ({$ref})";

            Mail::raw($body, function ($message) use ($subject, $storedPaths) {
                $message->to('geral@izzycar.pt')->subject($subject);
                foreach ($storedPaths as $absolutePath) {
                    $message->attach($absolutePath);
                }
            });

            // Guarda na base de dados
            $relativePhotoPaths = [];
            foreach ($storedPaths as $absolutePath) {
                $rel = str_replace(Storage::disk('public')->path(''), '', $absolutePath);
                $relativePhotoPaths[] = ltrim($rel, '/');
            }

            ConsignmentEvaluation::create([
                'reference'       => $ref,
                'brand'           => $validated['brand'],
                'model'           => $validated['model'],
                'version'         => $validated['version'] ?? null,
                'year'            => $validated['year'],
                'kilometers'      => $validated['kilometers'],
                'plate'           => $validated['plate'],
                'fuel'            => $validated['fuel'],
                'gearbox'         => $validated['gearbox'],
                'power'           => $validated['power'] ?? null,
                'displacement'    => $validated['displacement'] ?? null,
                'color'           => $validated['color'] ?? null,
                'condition'       => $validated['condition'],
                'description'     => $validated['description'] ?? null,
                'has_service_book'=> $request->has('has_service_book'),
                'has_2nd_key'     => $request->has('has_2nd_key'),
                'has_iuc'         => $request->has('has_iuc'),
                'has_inspection'  => $request->has('has_inspection'),
                'photos'          => $relativePhotoPaths,
                'name'            => $validated['name'],
                'phone'           => $validated['phone'],
                'email'           => $validated['email'],
                'location'        => $validated['location'] ?? null,
                'price_expectation'=> $validated['price_expectation'] ?? null,
                'status'          => 'novo',
            ]);

        } catch (\Throwable $e) {
            \Log::error('ConsignmentEvaluation: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Ocorreu um erro ao enviar o pedido. Por favor tente novamente ou contacte-nos directamente.',
            ], 500);
        }

        return response()->json(['status' => 'success']);
    }

    public function getConsignmentPage()
    {
        $data = Page::where('slug', 'consignacao-automovel')
            ->with('contents')
            ->firstOrFail();

        $data->contents = $data->contents->mapWithKeys(function ($content) {
            // Verifica se o campo é enum e se for obtém os valores
            if ($content->field_name == 'enum') {
                $content->field_name = 'enum';

                $pageController = new PageController();
                $contentEnum = $pageController->getEnumValues($content->field_value);

                return [$content->field_name => $contentEnum];
            }
            return [$content->field_name => $content->field_value];
        });

        return view('frontend.consignment', compact('data'));
    }
}
