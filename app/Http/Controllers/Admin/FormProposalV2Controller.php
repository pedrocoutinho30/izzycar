<?php

/**
 * ==================================================================
 * FORM PROPOSALS CONTROLLER V2
 * ==================================================================
 * 
 * Controller para formulários de proposta recebidos do site
 * 
 * @author Izzycar Team
 * @version 2.0
 * ==================================================================
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormProposal;
use App\Models\Client;
use Illuminate\Http\Request;

class FormProposalV2Controller extends Controller
{
    public function index(Request $request)
    {
        $query = FormProposal::orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        $formProposals = $query->paginate(15)->withQueryString();

        // Stats
        $stats = [
            ['title' => 'Total', 'value' => FormProposal::count(), 'icon' => 'envelope', 'color' => 'primary'],
            ['title' => 'Novos', 'value' => FormProposal::whereIn('status', ['novo', null])->count(), 'icon' => 'envelope-exclamation', 'color' => 'warning'],
            ['title' => 'Em Análise', 'value' => FormProposal::where('status', 'em_analise')->count(), 'icon' => 'hourglass-split', 'color' => 'info'],
            ['title' => 'Convertidos', 'value' => FormProposal::whereNotNull('proposal_id')->count(), 'icon' => 'check-circle', 'color' => 'success'],
        ];

        return view('admin.v2.form-proposals.index', compact('formProposals', 'stats'));
    }

    public function show($id)
    {
        $formProposal = FormProposal::findOrFail($id);
        return view('admin.v2.form-proposals.show', compact('formProposal'));
    }

    public function updateStatus(Request $request, $id)
    {
        $formProposal = FormProposal::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:novo,em_analise,convertido,rejeitado'
        ]);

        $formProposal->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Estado atualizado!');
    }

    public function destroy($id)
    {
        $formProposal = FormProposal::findOrFail($id);
        $formProposal->delete();

        return redirect()->route('admin.v2.form-proposals.index')
            ->with('success', 'Formulário eliminado!');
    }
}
