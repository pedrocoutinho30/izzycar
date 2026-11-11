<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\LeadActivity;
use App\Services\ClientMatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Recebe leads de sistemas externos (ex.: EVEXPERIENCE) por POST /api/lead.
 * Usa a regra única de duplicados (ClientMatcher): mesmo email OU telefone
 * reaproveita o registo existente e só regista o novo pedido na timeline.
 */
class LeadApiController extends Controller
{
    public const ORIGEM_PADRAO = 'EVEXPERIENCE';

    public function store(Request $request, ClientMatcher $matcher): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required_without:email|nullable|string|max:30',
            'email' => 'required_without:phone|nullable|email|max:255',
            'message' => 'nullable|string|max:2000',
            'source' => 'nullable|string|max:50',
        ]);

        $origem = filled($data['source'] ?? null) ? trim($data['source']) : self::ORIGEM_PADRAO;

        $match = $matcher->find($data['email'] ?? null, $data['phone'] ?? null);

        if ($match) {
            $matcher->complete($match->client, $data);
            $matcher->logMatch($match, $data['name'], "Lead {$origem}");
            $client = $match->client;
            $novo = false;
        } else {
            $client = Client::create([
                'name' => $data['name'],
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'origin' => $origem,
                'is_lead' => true,
                'lead_status' => 'nova',
                'lead_source' => $origem,
            ]);
            $novo = true;
        }

        LeadActivity::log(
            $client->id,
            "Lead recebida via API ({$origem})",
            filled($data['message'] ?? null) ? "Mensagem: {$data['message']}" : '',
            'bi-box-arrow-in-down',
            'primary'
        );

        return response()->json(['status' => 'success', 'id' => $client->id, 'created' => $novo], $novo ? 201 : 200);
    }
}
