<?php
// App/Http/Controllers/ProposalController.php
namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Mpdf;
use App\Models\Brand;
use App\Models\ConvertedProposal;
use App\Models\FormProposal;
use App\Models\VehicleAttribute;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\Browsershot\Browsershot;
use App\Models\ProposalAttributeValue;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProposalAcceptedMail;
use App\Models\Setting;
use App\Models\StatusProposalHistory;
use App\Services\ContractService;

class ProposalController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }
    public function index(Request $request)
    {
        // Obter os valores dos filtros
        $status = $request->input('status');
        $clientId = $request->input('client_id');

        // Consultar as propostas com base nos filtros
        $query = Proposal::query();

        if ($status) {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', 'Sem resposta');
        }

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        $proposals = $query->orderBy('created_at', 'desc')->paginate(10);

        // Obter a lista de clientes para o filtro
        $clients = Client::all();

        return view('proposals.index', compact('proposals', 'clients', 'status', 'clientId'));
    }

    public function create()
    {
        $clients = Client::all(); // To select a client
        $attributes = VehicleAttribute::all()->groupBy('attribute_group');
        $brands = Brand::with(['models' => function ($query) {
            $query->orderBy('name');
        }])->get();

        $commission_cost = Setting::where('label', 'commission_cost')->first()->value;
        $inspection_commission_cost = Setting::where('label', 'inspection_commission_cost')->first()->value;

        return view('proposals.form', compact('clients', 'brands', 'attributes', 'commission_cost', 'inspection_commission_cost'));
    }

    public function store(Request $request)
    {


        $request->validate([
            'client_id' => 'required',
            'brand' => 'required',
            'model' => 'required',
            'year' => 'integer',
            'mileage' => 'integer',
            'engine_capacity' => 'required',
            'co2' => 'required',
            'transport_cost' => 'required|numeric',
            'ipo_cost' => 'required|numeric',
            'imt_cost' => 'required|numeric',
            'registration_cost' => 'required|numeric',
            'isv_cost' => 'required|numeric',
            'license_plate_cost' => 'required|numeric',
            'inspection_commission_cost' => 'required|numeric',
            'commission_cost' => 'required|numeric',
            'proposed_car_mileage' => 'required|integer',
            'proposed_car_year_month' => 'required',
            'proposed_car_value' => 'required|numeric',
            'images' => 'nullable',
            'images.*' => 'nullable|image|mimes:webp,jpeg,png,jpg,gif,svg,avif|max:2048',
            'fuel' => 'nullable|in:Gasolina,Diesel,Híbrido Plug-in/Gasolina,Híbrido Plug-in/Diesel,Elétrico',
            'value' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'proposed_car_notes' => 'nullable|string',
            'url' => 'nullable|url',
            'status' => 'nullable|in:Pendente,Aprovada,Reprovada,Enviado,Sem resposta',
            'proposed_car_features' => 'nullable|string'
        ]);
        
        $proposal = Proposal::create([
            'url' => $request->url,
            'status' => $request->status,
            'client_id' => $request->client_id,
            'brand' => $request->brand,
            'model' => $request->model,
            'version' => $request->version,
            'year' => $request->year,
            'mileage' => $request->mileage,
            'engine_capacity' => $request->engine_capacity,
            'co2' => $request->co2,
            'transport_cost' => $request->transport_cost,
            'ipo_cost' => $request->ipo_cost,
            'imt_cost' => $request->imt_cost,
            'registration_cost' => $request->registration_cost,
            'isv_cost' => $request->isv_cost,
            'license_plate_cost' => $request->license_plate_cost,
            'inspection_commission_cost' => $request->inspection_commission_cost,
            'commission_cost' => $request->commission_cost,
            'proposed_car_mileage' => $request->proposed_car_mileage,
            'proposed_car_year_month' => $request->proposed_car_year_month,
            'proposed_car_value' => $request->proposed_car_value,
            'fuel' => $request->fuel,
            'value' => $request->value,
            'notes' => $request->notes,
            'proposed_car_notes' => $request->proposed_car_notes,
            'proposed_car_features' => $request->proposed_car_features,
            'images' => $request->images,
        ]);


        foreach ($request->input('attributes', []) as $attributeId => $value) {
            ProposalAttributeValue::create([
                'proposal_id' => $proposal->id,
                'attribute_id' => $attributeId,
                'value' => is_array($value) ? json_encode($value) : $value,
            ]);
        }

        // Salvar as imagens
        // if ($request->hasFile('images')) {
        //     $images = $request->file('images');
        //     $imagePaths = [];
        //     $i = 1;

        //     foreach ($images as $image) {
        //         // Garante extensão original
        //         $extension = $image->getClientOriginalExtension();

        //         // Nome personalizado
        //         $fileName = "{$proposal->brand}_{$proposal->model}_{$proposal->version}_{$proposal->proposed_car_year_month}_{$proposal->id}_{$i}." . $extension;

        //         // Caminho
        //         $path = $image->storeAs(
        //             "proposals/{$proposal->client_id}/{$proposal->id}", // diretório
        //             $fileName,                                         // nome do ficheiro
        //             'public'                                           // disco
        //         );

        //         $imagePaths[] = $path;
        //         $i++;
        //     }

        //     // Salvar os caminhos das imagens no banco
        //     $proposal->images = json_encode($imagePaths);
        //     $proposal->save();
        // }


        return redirect()->route('proposals.index')->with('success', 'Proposal created successfully.');
    }

    public function edit(Proposal $proposal)
    {
        $clients = Client::all(); // To select a client
        $images = [];
        if ($proposal->images) {
            $images = $proposal->images;
        }
        $attributes = VehicleAttribute::orderByRaw("FIELD(type, 'text', 'number', 'select', 'checkbox')")->get()->groupBy('attribute_group');

        $attributeValues = $proposal->attributeValues->keyBy('attribute_id');
        $brands = Brand::with(['models' => function ($query) {
            $query->orderBy('name');
        }])->get();
         $commission_cost = Setting::where('label', 'commission_cost')->first()->value;
         $inspection_commission_cost = Setting::where('label', 'inspection_commission_cost')->first()->value;
        return view('proposals.form', compact('proposal', 'clients', 'images', 'brands', 'attributes', 'attributeValues', 'commission_cost', 'inspection_commission_cost'));
    }

    public function updateStatus(Request $request, Proposal $proposal)
    {
        $request->validate([
            'status' => 'required|in:Pendente,Aprovada,Reprovada,Enviado,Sem resposta',
        ]);

        $proposal->update(['status' => $request->status]);

        return response()->json(['success' => 'Estado atualizado com sucesso.']);
    }
    public function update(Request $request, Proposal $proposal)
    {
        $request->validate([
            'url' => 'nullable|url',
            'status' => 'nullable|in:Pendente,Aprovada,Reprovada,Enviado,Sem resposta',
            'client_id' => 'required',
            'brand' => 'required',
            'model' => 'required',
            'year' => 'integer',
            'mileage' => 'integer',
            'engine_capacity' => 'required',
            'co2' => 'required',
            'transport_cost' => 'required|numeric',
            'ipo_cost' => 'required|numeric',
            'imt_cost' => 'required|numeric',
            'registration_cost' => 'required|numeric',
            'isv_cost' => 'required|numeric',
            'license_plate_cost' => 'required|numeric',
            'inspection_commission_cost' => 'required|numeric',
            'commission_cost' => 'required|numeric',
            'proposed_car_mileage' => 'required|integer',
            'proposed_car_year_month' => 'required',
            'proposed_car_value' => 'required|numeric',
            'images' => 'nullable',
            'images.*' => 'nullable|image|mimes:webp,jpeg,png,jpg,gif,svg,avif|max:16000',
            'fuel' => 'nullable|in:Gasolina,Diesel,Híbrido Plug-in/Gasolina,Híbrido Plug-in/Diesel,Elétrico',
            'value' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'proposed_car_notes' => 'nullable|string',
            'proposed_car_features' => 'nullable|string'
        ]);

        $proposal->update([
            'url' => $request->url,
            'status' => $request->status,
            'client_id' => $request->client_id,
            'brand' => $request->brand,
            'model' => $request->model,
            'version' => $request->version,
            'year' => $request->year,
            'mileage' => $request->mileage,
            'engine_capacity' => $request->engine_capacity,
            'co2' => $request->co2,
            'transport_cost' => $request->transport_cost,
            'ipo_cost' => $request->ipo_cost,
            'imt_cost' => $request->imt_cost,
            'registration_cost' => $request->registration_cost,
            'isv_cost' => $request->isv_cost,
            'license_plate_cost' => $request->license_plate_cost,
            'inspection_commission_cost' => $request->inspection_commission_cost,
            'commission_cost' => $request->commission_cost,
            'proposed_car_mileage' => $request->proposed_car_mileage,
            'proposed_car_year_month' => $request->proposed_car_year_month,
            'proposed_car_value' => $request->proposed_car_value,
            'fuel' => $request->fuel,
            'value' => $request->value,
            'notes' => $request->notes,
            'proposed_car_notes' => $request->proposed_car_notes,
            'proposed_car_features' => $request->proposed_car_features,
            'images' => $request->images,
        ]);


        // Remove os antigos
        $proposal->attributeValues()->delete();
        foreach ($request->input('attributes', []) as $attributeId => $value) {
            if ($value === null) {
                continue; // Ignorar valores nulos
            }
            ProposalAttributeValue::create([
                'proposal_id' => $proposal->id,
                'attribute_id' => $attributeId,
                'value' => is_array($value) ? json_encode($value) : $value,
            ]);
        }

        // Excluir as imagens selecionadas para remoção
        // if ($request->has('delete_images')) {
        //     foreach ($request->delete_images as $imageToDelete) {
        //         if (Storage::exists('public/' . $imageToDelete)) {
        //             Storage::delete('public/' . $imageToDelete);
        //         }
        //     }
        // }

        // Adicionar novas imagens no update
        // if ($request->hasFile('images')) {
        //     $images = $request->file('images');

        //     // Pegar as imagens já existentes
        //     $existingImages = $proposal->images ? json_decode($proposal->images, true) : [];
        //     $imagePaths = $existingImages;

        //     // Descobrir quantas já existem para continuar a numeração
        //     $i = count($existingImages) + 1;

        //     foreach ($images as $image) {
        //         // Extensão do ficheiro original
        //         $extension = $image->getClientOriginalExtension();

        //         // Nome personalizado: brand_model_version_id_numeração
        //         $fileName = "{$proposal->brand}_{$proposal->model}_{$proposal->version}_{$proposal->proposed_car_year_month}_{$proposal->id}_{$i}." . $extension;

        //         // Guardar na pasta correta
        //         $path = $image->storeAs(
        //             "proposals/{$request->client_id}/{$proposal->id}",
        //             $fileName,
        //             'public'
        //         );

        //         $imagePaths[] = $path;
        //         $i++;
        //     }

        //     // Atualizar as imagens no banco de dados
        //     $proposal->images = json_encode($imagePaths);
        //     $proposal->save();
        // }
        return redirect()->route('proposals.index')->with('success', 'Proposal updated successfully.');
    }

    public function destroy(Proposal $proposal)
    {
        $proposal->delete();
        return redirect()->route('proposals.index')->with('success', 'Proposal deleted successfully.');
    }

    public function generatePdf($id)
    {


        $proposal = Proposal::findOrFail($id);

        $attributes = [];

        $potencia = "";
        $caixa = "";
        $cilindrada = "";
        foreach ($proposal->attributeValues as $attributeValue) {


            $attribute = VehicleAttribute::find($attributeValue->attribute_id);
            if (!$attribute) continue;

            $group = $attribute->attribute_group ?? 'Outros'; // fallback caso esteja vazio
            $name = $attribute->name;
            if ($attribute->key == 'potencia') {
                $potencia = $attributeValue->value;
            }

            if ($attribute->key  == 'tipo_caixa') {
                $caixa = $attributeValue->value;
            }
            if ($attribute->key == 'cilindrada') {
                $cilindrada = $attributeValue->value;
            }
            // Inicializa o grupo se não existir
            if (!isset($attributes[$group])) {
                $attributes[$group] = [];
            }
            if ($attribute->type == 'select') {
                $attributes[$group][$name] = $attributeValue->value;
                continue;
            } elseif ($attribute->type == 'boolean') {
                $attributes[$group][$name] = $name;
                continue;
            } else if ($attribute->type == 'text' || $attribute->type == 'number') {
                $attributes[$group][$name] = $attributeValue->value;
                continue;
            }
            // Adiciona o nome do atributo e o valor no grupo correto
            $attributes[$group][$name] = $attributeValue->value;
        }

        $order = [
            'Dados do Veículo',
            'Características Técnicas',
            'Segurança & Desempenho',
            'Conforto & Multimédia',
            'Equipamento Interior',
            'Equipamento Exterior',
            'Outros Extras'
        ];

        // Função para ordenar o array associativo $attributes
        uksort($attributes, function ($a, $b) use ($order) {
            $posA = array_search($a, $order);
            $posB = array_search($b, $order);

            // Se algum grupo não estiver na lista, joga ele para o fim
            if ($posA === false) $posA = count($order);
            if ($posB === false) $posB = count($order);

            return $posA <=> $posB;
        });



        return view('proposals.pdf', compact('proposal', 'attributes', 'potencia', 'caixa', 'cilindrada'));

        $html = view('proposals.pdf', compact('proposal'))->render();


        // Salvar como PDF
        return view('proposals.pdf', compact('proposal'));


        $pdf = Pdf::loadView('proposals.pdf', compact('proposal'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('Proposta' . ' Izzycar ' . $proposal->brand . '_' . $proposal->model  . '.pdf');

        // $html = view('proposals.pdf', compact('proposal'))->render();
        // // Instanciar o mpdf
        $mpdf = new \Mpdf\Mpdf();
        // // Escrever o conteúdo HTML no PDF
        $mpdf->WriteHTML($html);

        // // Gerar o PDF e mostrar no navegador
        return $mpdf->Output('Proposta' . ' Izzycar ' . $proposal->brand . '_' . $proposal->model  . '.pdf', 'I'); // 'I' mostra no navegador
    }

    public function detail($brand, $model, $version, $id)
    {
        $proposal = Proposal::where('id', $id)
            ->firstOrFail();

        $attributes = [];

        $potencia = "";
        $caixa = "";
        $cilindrada = "";
        foreach ($proposal->attributeValues as $attributeValue) {


            $attribute = VehicleAttribute::find($attributeValue->attribute_id);
            if (!$attribute) continue;

            $group = $attribute->attribute_group ?? 'Outros'; // fallback caso esteja vazio
            $name = $attribute->name;
            if ($attribute->key == 'potencia') {
                $potencia = $attributeValue->value;
            }

            if ($attribute->key  == 'tipo_caixa') {
                $caixa = $attributeValue->value;
            }
            if ($attribute->key == 'cilindrada') {
                $cilindrada = $attributeValue->value;
            }
            // Inicializa o grupo se não existir
            if (!isset($attributes[$group])) {
                $attributes[$group] = [];
            }
            if ($attribute->type == 'select') {
                $attributes[$group][$name] = $attributeValue->value;
                continue;
            } elseif ($attribute->type == 'boolean') {
                $attributes[$group][$name] = $name;
                continue;
            } else if ($attribute->type == 'text' || $attribute->type == 'number') {
                $attributes[$group][$name] = $attributeValue->value;
                continue;
            }
            // Adiciona o nome do atributo e o valor no grupo correto
            $attributes[$group][$name] = $attributeValue->value;
        }

        $order = [
            'Dados do Veículo',
            'Características Técnicas',
            'Segurança & Desempenho',
            'Conforto & Multimédia',
            'Equipamento Interior',
            'Equipamento Exterior',
            'Outros Extras'
        ];

        // Função para ordenar o array associativo $attributes
        uksort($attributes, function ($a, $b) use ($order) {
            $posA = array_search($a, $order);
            $posB = array_search($b, $order);

            // Se algum grupo não estiver na lista, joga ele para o fim
            if ($posA === false) $posA = count($order);
            if ($posB === false) $posB = count($order);

            return $posA <=> $posB;
        });

        $client = Client::find($proposal->client_id);

        return view('proposals.view-proposal', compact('proposal', 'attributes', 'potencia', 'caixa', 'cilindrada', 'client'));
    }


    public function downloadPdf()
    {
        $pdfPath = storage_path("app/proposal-te.pdf");
        // Gerar o PDF usando Browsershot
        // Certifique-se de que a URL corresponde à rota que gera o PDF
        // Ajuste a URL conforme necessário para corresponder à sua rota
        // Exemplo: http://127.0.0.1:8000/admin/proposals/70/download-pdf
        Browsershot::url('http://127.0.0.1:8000/proposals/70/download-pdf') // URL da rota que retorna HTML
            ->waitUntilNetworkIdle()
            ->save($pdfPath);

        return response()->download($pdfPath);
    }


    public function duplicate($id)
    {
        // Buscar a proposta original
        $originalProposal = Proposal::findOrFail($id);

        // Criar uma nova instância apenas com os campos desejados
        $newProposal = Proposal::create([
            'client_id'      => $originalProposal->client_id,
            'brand'          => $originalProposal->brand,
            'model'          => $originalProposal->model,
            'year'           => $originalProposal->year,
            'mileage'        => $originalProposal->mileage,
            'engine_capacity' => $originalProposal->engine_capacity,
            'co2'            => $originalProposal->co2,
            'transport_cost' => $originalProposal->transport_cost,
            'ipo_cost'       => $originalProposal->ipo_cost,
            'imt_cost'       => $originalProposal->imt_cost,
            'registration_cost' => $originalProposal->registration_cost,
            'isv_cost'       => $originalProposal->isv_cost,
            'license_plate_cost' => $originalProposal->license_plate_cost,
            'fuel'           => $originalProposal->fuel,
            'value'          => $originalProposal->value,
            'notes'          => $originalProposal->notes,
            'proposed_car_value' => 0,
            'inspection_commission_cost' => 0,
            'commission_cost' => 0,
            'proposed_car_mileage' => 0,
            'proposed_car_year_month' => '',
            'proposed_car_notes' => '',
            'url'            => '',
            'status'         => 'Pendente',
            'images'         => [],
            'proposed_car_features' => ''

        ]);

        // Redirecionar para a página de edição da nova proposta (ou onde preferir)
        return redirect()->route('proposals.edit', $newProposal->id)->with('success', 'Proposta duplicada com sucesso!');
    }

    // public function accept(Proposal $proposal)
    // {

    //     /**
    //      * 1. mudar estado de proposta para Aprovada
    //      * 2. Criar registo nas propostas aceites com apenas informação importante (apresentar ja valores que tenho de receber de inicio, valores que vão ser só pagos no final, todos os valores separados para ir marcando o que ja está ou mão pago, etc.)
    //      * 3. enviar notificação para o cliente com o status da proposta e com contrato de serviço
    //      * 4. enviar email para mim para ter conhecimento da aceitação
    //      */
    //     $proposal->status = 'Aprovada';
    //     $proposal->save();

    //     $valor_segunda_tranche = $valor_primeira_tranche = ($proposal->transport_cost + $proposal->inspection_commission_cost + $proposal->ipo_cost +  $proposal->imt_cost + +$proposal->registration_cost + +$proposal->license_plate_cost + $proposal->commission_cost) * 0.5; // 50% do valor do carro
    //     // Criar registo nas propostas aceites
    //     if (ConvertedProposal::where('proposal_id', $proposal->id)->exists()) {
    //         return back()->with('error', 'A proposta já foi aceite anteriormente.');
    //     }

    //     $convertedProposal = ConvertedProposal::create([
    //         'proposal_id' => $proposal->id,
    //         'client_id' => $proposal->client_id,
    //         'status' => 'Iniciada',
    //         'brand' => $proposal->brand,
    //         'modelCar' => $proposal->model,
    //         'version' => $proposal->version,
    //         'year' => $proposal->proposed_car_year_month,
    //         'km' => $proposal->proposed_car_mileage,
    //         'url' => $proposal->url,
    //         'custo_inspecao_origem' => $proposal->inspection_commission_cost,
    //         'custo_transporte' => $proposal->transport_cost,
    //         'custo_ipo' => $proposal->ipo_cost,
    //         'isv' => $proposal->isv_cost,
    //         'custo_imt' => $proposal->imt_cost,
    //         'custo_matricula' => $proposal->license_plate_cost,
    //         'custo_registo_automovel' => $proposal->registration_cost,
    //         'valor_primeira_tranche' => $valor_primeira_tranche,
    //         'valor_segunda_tranche' => $valor_segunda_tranche,
    //         'valor_carro' => $proposal->proposed_car_value,
    //         'valor_comissao' => $proposal->commission_cost
    //     ]);


    //     // Enviar notificação para o cliente
    //     // Notification::send($proposal->client, new ProposalAccepted($proposal));
    //     $this->sendAcceptanceEmail($proposal->id, $proposal->client_id, $convertedProposal);

    //     // // Enviar email para o admin
    //     Mail::raw(
    //         "Proposta {$proposal->id} foi aceite. " . route('converted-proposals.edit', [$convertedProposal->id]),
    //         function ($mail) {
    //             $mail->to('geral@izzycar.pt')
    //                 ->subject('Proposta aceite - Izzycar');
    //         }
    //     );
    //     return back()->with('error', 'A proposta foi aceite. Irá receber confirmação por email e entraremos em contacto brevemente.');
    // }


    // public function sendAcceptanceEmail($proposalId, $client_id, $convertedProposal)
    // {
    //     $client = Client::find($client_id);
    //     $proposal = Proposal::find($proposalId);
    //     // Aqui vais buscar os dados reais (para já fixo)
    //     $data = [
    //         'client_name' => $client->name,
    //         'brand' => $convertedProposal->brand,
    //         'model' => $convertedProposal->modelCar,
    //         'version' => $convertedProposal->version,
    //         'car_image' => is_array(json_decode($proposal->images, true)) ? json_decode($proposal->images, true)[0] ?? null : null,
    //     ];
    //     // Gerar PDF do contrato (podes usar dompdf)
    //     // $pdfContent = app(ClientController::class)->generateContractPdf($client_id);
    //     $pdfContent = ContractService::generateContractPdf($client, $convertedProposal);
    //     $settings = Setting::where('label', 'email')->first();
    //     Mail::to("pedroc_30@hotmail.com")->send(new ProposalAcceptedMail($convertedProposal, $pdfContent, $data));
    // }



    public function accept(Proposal $proposal, Request $request)
    {
        //0. Atualizar dados do cliente
        $input = $request->only([
            'email',
            'address',
            'postal_code',
            'city',
            'identification_number',
            'phone',
            'vat_number'

        ]);

        // Remove valores vazios
        $input = array_filter($input, fn($value) => !is_null($value) && $value !== '');

        $client = Client::find($proposal->client_id);

        $client->update($input);

        // 1. Atualizar estado
        $proposal->status = 'Aprovada';
        $proposal->save();


        // 2. Criar registo em propostas aceites
        if (ConvertedProposal::where('proposal_id', $proposal->id)->exists()) {
            return back()->with('error', 'A proposta já foi aceite anteriormente.');
        }

        $valor_total = $proposal->transport_cost
            + $proposal->inspection_commission_cost
            + $proposal->ipo_cost
            + $proposal->imt_cost
            + $proposal->registration_cost
            + $proposal->license_plate_cost
            + $proposal->commission_cost;

        $valor_primeira_tranche = $valor_segunda_tranche = $valor_total * 0.5;

        $convertedProposal = ConvertedProposal::create([
            'proposal_id' => $proposal->id,
            'client_id' => $proposal->client_id,
            'status' => 'Iniciada',
            'brand' => $proposal->brand,
            'modelCar' => $proposal->model,
            'version' => $proposal->version,
            'year' => $proposal->proposed_car_year_month,
            'km' => $proposal->proposed_car_mileage,
            'url' => $proposal->url,
            'custo_inspecao_origem' => $proposal->inspection_commission_cost,
            'custo_transporte' => $proposal->transport_cost,
            'custo_ipo' => $proposal->ipo_cost,
            'isv' => $proposal->isv_cost,
            'custo_imt' => $proposal->imt_cost,
            'custo_matricula' => $proposal->license_plate_cost,
            'custo_registo_automovel' => $proposal->registration_cost,
            'valor_primeira_tranche' => $valor_primeira_tranche,
            'valor_segunda_tranche' => $valor_segunda_tranche,
            'valor_carro' => $proposal->proposed_car_value,
            'valor_comissao' => $proposal->commission_cost,
        ]);

        //Criar registo de historico
        StatusProposalHistory::create([
            'new_status' => 'Iniciada',
            'old_status' => null,
            'converted_proposal_id' => $convertedProposal->id,
        ]);
        // 3. Enviar notificação para o cliente
        $this->sendAcceptanceEmail($proposal, $convertedProposal);

        // 4. Enviar email para o admin
        Mail::raw(
            "Proposta {$proposal->id} foi aceite. " . route('converted-proposals.edit', [$convertedProposal->id]),
            function ($mail) {
                $mail->to('geral@izzycar.pt')
                    ->subject('Proposta aceite - Izzycar');
            }
        );

        return back()->with('success', 'A proposta foi aceite. O cliente receberá um email com o contrato.');
    }

    public function sendAcceptanceEmail($proposal, $convertedProposal)
    {
        $client = Client::find($proposal->client_id);

        $data = [
            'client_name' => $client->name,
            'brand' => $convertedProposal->brand,
            'model' => $convertedProposal->modelCar,
            'version' => $convertedProposal->version,
            'car_image' => is_array(json_decode($proposal->images, true))
                ? json_decode($proposal->images, true)[0] ?? null
                : null,
        ];

        // Gerar PDF em memória
        $pdfContent = ContractService::generateContractPdf($client);

        // Enviar para o cliente e em cc para admin
        Mail::to($client->email)
            ->cc('geral@izzycar.pt')
            ->send(new ProposalAcceptedMail($convertedProposal, $pdfContent, $data));
    }
    public function sentWhatsapp($id)
    {
        dd($id);
    }


    public function create_by_form(Request $request)
    {


        $form = $request->all()['form'];
        $proposal = new Proposal();
        $proposal->client_id = $form['client_id'] ?? null;
        $proposal->brand = $form['brand'] ?? null;
        $proposal->model = $form['model'] ?? null;
        $proposal->version = $form['version'] ?? null;
        $proposal->fuel = $form['fuel'] ?? null;
        $proposal->year = $form['year_min'] ?? null;
        $proposal->mileage = $form['km_max'] ?? null;
        $proposal->status = 'Pendente';
        $proposal->notes = "Orçamento via formulário: \n" . ($form['budget'] ?? '') . "\nCor: " . ($form['color'] ?? '') . "\nCaixa: " . ($form['gearbox'] ?? '') . "\nExtras: " . ($form['extras'] ?? '');
        $proposal->engine_capacity = 0;
        $proposal->co2 = 0;
        $proposal->transport_cost = 1250;
        $proposal->ipo_cost = 100;
        $proposal->imt_cost = 65;
        $proposal->registration_cost = 55;
        $proposal->isv_cost = 0;
        $proposal->license_plate_cost = 40;
        $proposal->inspection_commission_cost = 350;
        $proposal->commission_cost = 861;
        $proposal->proposed_car_mileage = 0;
        $proposal->proposed_car_year_month = 0;
        $proposal->proposed_car_value = 0;
        $proposal->save();
        $formProposal = FormProposal::findOrFail($form['id']); // Create the Form
        $formProposal->proposal_id = $proposal->id;
        $formProposal->save();
        return $proposal;
    }
}
