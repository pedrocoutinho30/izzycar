<?php
// App/Http/Controllers/ProposalController.php
namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Mpdf;
use App\Models\Brand;
use App\Models\VehicleAttribute;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\Browsershot\Browsershot;
use App\Models\ProposalAttributeValue;

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
        return view('proposals.form', compact('clients', 'brands', 'attributes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required',
            'brand' => 'required',
            'model' => 'required',
            'year' => 'required|integer',
            'mileage' => 'required|integer',
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
            'images' => 'nullable|array',
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
            'proposed_car_features' => $request->proposed_car_features
        ]);


        foreach ($request->input('attributes', []) as $attributeId => $value) {
            ProposalAttributeValue::create([
                'proposal_id' => $proposal->id,
                'attribute_id' => $attributeId,
                'value' => is_array($value) ? json_encode($value) : $value,
            ]);
        }

        // Salvar as imagens
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            $imagePaths = [];

            foreach ($images as $image) {
                $path = $image->store('proposals/' . $request->client_id . '/' . $proposal->id, 'public');
                $imagePaths[] = $path;
            }

            // Salvar os caminhos das imagens no banco de dados
            $proposal->images = json_encode($imagePaths);
            $proposal->save();
        }



        return redirect()->route('proposals.index')->with('success', 'Proposal created successfully.');
    }

    public function edit(Proposal $proposal)
    {
        $clients = Client::all(); // To select a client
        $images = [];
        if ($proposal->images) {
            $images = json_decode($proposal->images);
        }

        $attributes = VehicleAttribute::orderByRaw("FIELD(type, 'text', 'number', 'select', 'checkbox')")->get()->groupBy('attribute_group');

        $attributeValues = $proposal->attributeValues->keyBy('attribute_id');
        $brands = Brand::with(['models' => function ($query) {
            $query->orderBy('name');
        }])->get();
        return view('proposals.form', compact('proposal', 'clients', 'images', 'brands', 'attributes', 'attributeValues'));
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
            'year' => 'required|integer',
            'mileage' => 'required|integer',
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
            'images' => 'nullable|array',
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
            'proposed_car_features' => $request->proposed_car_features
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
        if ($request->has('delete_images')) {
            foreach ($request->delete_images as $imageToDelete) {
                if (Storage::exists('public/' . $imageToDelete)) {
                    Storage::delete('public/' . $imageToDelete);
                }
            }
        }

        // Adicionar novas imagens
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            $imagePaths = [];

            foreach ($images as $image) {
                // Armazenar cada imagem na pasta correta
                $path = $image->store('proposals/' . $request->client_id . '/' . $proposal->id, 'public');
                $imagePaths[] = $path;
            }

            // Atualizar as imagens no banco de dados
            $proposal->images = json_encode($imagePaths);
            $proposal->save();
        }
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

    public function sentWhatsapp($id)
    {

        DD($id);
    }
}
