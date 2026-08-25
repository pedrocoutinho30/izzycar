<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CarCandidate;
use App\Models\Client;
use Illuminate\Http\Request;

/**
 * "Carros em Análise" — lista de anúncios de carros que o admin/angariador
 * foi vendo para um determinado Cliente/Lead (o mesmo modelo Client),
 * reordenável manualmente. Usado a partir das páginas de detalhe de
 * Cliente e de Lead (ambas incluem o mesmo partial).
 */
class CarCandidateController extends Controller
{
    private function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'link' => 'nullable|url|max:2048',
            'price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:2000',
            'status' => 'required|in:' . implode(',', array_keys(CarCandidate::STATUS_OPTIONS)),
            'notes' => 'nullable|string|max:2000',
        ];
    }

    /**
     * Mensagens à mão porque o site não tem lang/pt/validation.php — sem
     * isto, os erros apareciam como a chave crua (ex. "validation.required").
     */
    private function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'title.max' => 'O título não pode ter mais de 255 caracteres.',
            'link.url' => 'O link tem de ser um URL válido (ex.: https://...).',
            'price.numeric' => 'O preço tem de ser um número.',
            'price.min' => 'O preço não pode ser negativo.',
            'status.required' => 'Escolha um estado.',
            'status.in' => 'Estado inválido.',
        ];
    }

    public function store(Request $request, Client $client)
    {
        $data = $request->validate($this->rules(), $this->messages());

        $client->carCandidates()->create($data + [
            'order_position' => $client->carCandidates()->count(),
        ]);

        return redirect()->back()->with('success', 'Carro adicionado.')->withFragment('car-candidates');
    }

    public function update(Request $request, Client $client, CarCandidate $carCandidate)
    {
        abort_unless($carCandidate->client_id === $client->id, 404);

        $data = $request->validate($this->rules(), $this->messages());
        $carCandidate->update($data);

        return redirect()->back()->with('success', 'Carro atualizado.')->withFragment('car-candidates');
    }

    public function destroy(Client $client, CarCandidate $carCandidate)
    {
        abort_unless($carCandidate->client_id === $client->id, 404);

        $carCandidate->delete();

        return redirect()->back()->with('success', 'Carro removido.')->withFragment('car-candidates');
    }

    public function reorder(Request $request, Client $client)
    {
        $request->validate(['order' => 'required|array']);

        foreach ($request->order as $position => $carCandidateId) {
            CarCandidate::where('client_id', $client->id)
                ->where('id', $carCandidateId)
                ->update(['order_position' => (int) $position]);
        }

        return response()->json(['success' => true]);
    }
}
