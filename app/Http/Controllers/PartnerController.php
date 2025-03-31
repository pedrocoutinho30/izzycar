<?php

// app/Http/Controllers/PartnerController.php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function index()
    {
        $partners = Partner::all();
        return view('partners.index', compact('partners'));
    }

    public function create()
    {
        return view('partners.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'postal_code' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'vat' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:255',
        ]);

        Partner::create($validated);

        return redirect()->route('partners.index')->with('success', 'Parceiro criado com sucesso!');
    }

    public function edit(Partner $partner)
    {
        return view('partners.form', compact('partner'));
    }

    public function update(Request $request, Partner $partner)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'postal_code' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'vat' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:255',
        ]);

        $partner->update($validated);

        return redirect()->route('partners.index')->with('success', 'Parceiro atualizado com sucesso!');
    }

    public function destroy(Partner $partner)
    {
        $partner->delete();

        return redirect()->route('partners.index')->with('success', 'Parceiro excluído com sucesso!');
    }
}
