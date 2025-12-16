<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;

/**
 * PartnerV2Controller
 * 
 * Controlador para gestão de parceiros no sistema V2
 * Parceiros são entidades externas que colaboram com a empresa
 * (ex: despachantes, seguradoras, oficinas, etc.)
 */
class PartnerV2Controller extends Controller
{
    /**
     * Lista todos os parceiros com filtros e paginação
     */
    public function index(Request $request)
    {
        // Query base
        $query = Partner::query();

        // Filtro de pesquisa
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_name', 'like', "%{$search}%")
                  ->orWhere('vat', 'like', "%{$search}%");
            });
        }

        // Paginação
        $partners = $query->orderBy('created_at', 'desc')
                         ->paginate(12)
                         ->withQueryString();

        // Estatísticas
        $stats = [
            [
                'title' => 'Total de Parceiros',
                'value' => Partner::count(),
                'color' => 'primary',
                'icon' => 'bi-handshake'
            ],
            [
                'title' => 'Novos este Mês',
                'value' => Partner::whereMonth('created_at', now()->month)->count(),
                'color' => 'success',
                'icon' => 'bi-calendar-check'
            ]
        ];

        return view('admin.v2.partners.index', compact('partners', 'stats'));
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        return view('admin.v2.partners.form');
    }

    /**
     * Guarda novo parceiro
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'vat' => 'nullable|string|max:50',
            'contact_name' => 'nullable|string|max:255',
        ]);

        Partner::create($validated);

        return redirect()->route('admin.v2.partners.index')
                        ->with('success', 'Parceiro criado com sucesso!');
    }

    /**
     * Formulário de edição
     */
    public function edit($id)
    {
        $partner = Partner::findOrFail($id);
        return view('admin.v2.partners.form', compact('partner'));
    }

    /**
     * Atualiza parceiro
     */
    public function update(Request $request, $id)
    {
        $partner = Partner::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'vat' => 'nullable|string|max:50',
            'contact_name' => 'nullable|string|max:255',
        ]);

        $partner->update($validated);

        return redirect()->route('admin.v2.partners.index')
                        ->with('success', 'Parceiro atualizado com sucesso!');
    }

    /**
     * Elimina parceiro
     */
    public function destroy($id)
    {
        $partner = Partner::findOrFail($id);
        $partner->delete();

        return redirect()->route('admin.v2.partners.index')
                        ->with('success', 'Parceiro eliminado com sucesso!');
    }
}
