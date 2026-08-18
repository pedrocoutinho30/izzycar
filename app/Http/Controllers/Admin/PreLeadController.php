<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\LeadActivity;
use App\Models\PreLead;
use Illuminate\Http\Request;

/**
 * Gestão dos pré-leads criados automaticamente pelo webhook do WhatsApp
 * (ver App\Http\Controllers\Api\WhatsAppWebhookController). Aprovar cria um
 * Lead real; Rejeitar apaga o registo sem criar nada — em ambos os casos o
 * pré-lead deixa de existir.
 */
class PreLeadController extends Controller
{
    public function index()
    {
        $preLeads = PreLead::where('status', 'pendente')->latest()->get();

        return view('admin.v2.pre-leads.index', compact('preLeads'));
    }

    public function approve(Request $request, PreLead $preLead)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $lead = Client::create([
            'name' => $data['name'],
            'phone' => $preLead->phone,
            'is_lead' => true,
            'lead_source' => 'whatsapp',
            'lead_status' => 'nova',
            'origin' => 'WhatsApp',
            'observation' => $preLead->message,
        ]);

        LeadActivity::log(
            $lead->id,
            'Lead criado a partir de mensagem WhatsApp',
            'Pré-lead aprovado por ' . (auth()->user()->name ?? '—') . '. Mensagem original: ' . ($preLead->message ?? '—'),
            'bi-whatsapp',
            'success'
        );

        $preLead->delete();

        return redirect()->route('admin.v2.pre-leads.index')->with('success', 'Pré-lead aprovado e convertido em lead.');
    }

    public function reject(PreLead $preLead)
    {
        $preLead->delete();

        return redirect()->route('admin.v2.pre-leads.index')->with('success', 'Pré-lead rejeitado.');
    }
}
