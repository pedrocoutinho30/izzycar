<?php
// App/Http/Controllers/ProposalController.php
namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Mpdf;


class ProposalController extends Controller
{
    public function index()
    {
        $proposals = Proposal::all();
        return view('proposals.index', compact('proposals'));
    }

    public function create()
    {
        $clients = Client::all(); // To select a client
        return view('proposals.form', compact('clients'));
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
            'images.*' => 'nullable|image|mimes:webp,jpeg,png,jpg,gif,svg|max:2048',
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



        return view('proposals.form', compact('proposal', 'clients', 'images'));
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
            'images.*' => 'nullable|image|mimes:webp,jpeg,png,jpg,gif,svg|max:6000',
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

    public function downloadPdf($id)
    {
        $proposal = Proposal::findOrFail($id);
        $html = view('proposals.pdf', compact('proposal'))->render();

        // Instanciar o mpdf
        $mpdf = new \Mpdf\Mpdf();

        // Escrever o conteúdo HTML no PDF
        $mpdf->WriteHTML($html);

        // Gerar o PDF e mostrar no navegador
        return $mpdf->Output('Proposta' . '_' . $proposal->id . '_' . $proposal->client->name . '_ ' . $proposal->brand . '_' . $proposal->model  . '.pdf', 'I'); // 'I' mostra no navegador
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
}
