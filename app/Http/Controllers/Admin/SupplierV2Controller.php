<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

/**
 * SupplierV2Controller
 * 
 * Controlador para gestão de fornecedores no sistema V2
 * Fornece funcionalidades CRUD completas com interface moderna e mobile-first
 * 
 * Rotas:
 * - GET    /gestao/v2/suppliers          -> index()   (listagem)
 * - GET    /gestao/v2/suppliers/create   -> create()  (formulário de criação)
 * - POST   /gestao/v2/suppliers          -> store()   (guardar novo)
 * - GET    /gestao/v2/suppliers/{id}/edit -> edit()   (formulário de edição)
 * - PUT    /gestao/v2/suppliers/{id}     -> update()  (atualizar)
 * - DELETE /gestao/v2/suppliers/{id}     -> destroy() (eliminar)
 */
class SupplierV2Controller extends Controller
{
    /**
     * Lista todos os fornecedores com filtros e paginação
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Query base
        $query = Supplier::query();

        // Filtro de pesquisa (nome da empresa ou contacto)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('contact_name', 'like', "%{$search}%")
                  ->orWhere('vat', 'like', "%{$search}%");
            });
        }

        // Filtro por tipo
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Paginação
        $suppliers = $query->orderBy('created_at', 'desc')
                          ->paginate(12)
                          ->withQueryString();

        // Estatísticas para os cards
        $stats = [
            [
                'title' => 'Total de Fornecedores',
                'value' => Supplier::count(),
                'color' => 'primary',
                'icon' => 'bi-truck'
            ],
            [
                'title' => 'Nacionais',
                'value' => Supplier::where('country', 'Portugal')->count(),
                'color' => 'success',
                'icon' => 'bi-flag'
            ],
            [
                'title' => 'Internacionais',
                'value' => Supplier::where('country', '!=', 'Portugal')->orWhereNull('country')->count(),
                'color' => 'info',
                'icon' => 'bi-globe'
            ]
        ];

        return view('admin.v2.suppliers.index', compact('suppliers', 'stats'));
    }

    /**
     * Mostra o formulário de criação
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.v2.suppliers.form');
    }

    /**
     * Guarda um novo fornecedor
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validação dos dados
        $validated = $request->validate([
            'type' => 'nullable|string|max:255',
            'company_name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'country' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'vat' => 'nullable|string|max:50',
            'identification_number' => 'nullable|string|max:50',
            'identification_number_validity' => 'nullable|date',
        ]);

        // Criar fornecedor
        Supplier::create($validated);

        return redirect()->route('admin.v2.suppliers.index')
                        ->with('success', 'Fornecedor criado com sucesso!');
    }

    /**
     * Mostra o formulário de edição
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('admin.v2.suppliers.form', compact('supplier'));
    }

    /**
     * Atualiza um fornecedor existente
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        // Validação
        $validated = $request->validate([
            'type' => 'nullable|string|max:255',
            'company_name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'country' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'vat' => 'nullable|string|max:50',
            'identification_number' => 'nullable|string|max:50',
            'identification_number_validity' => 'nullable|date',
        ]);

        // Atualizar
        $supplier->update($validated);

        return redirect()->route('admin.v2.suppliers.index')
                        ->with('success', 'Fornecedor atualizado com sucesso!');
    }

    /**
     * Elimina um fornecedor
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return redirect()->route('admin.v2.suppliers.index')
                        ->with('success', 'Fornecedor eliminado com sucesso!');
    }
}
