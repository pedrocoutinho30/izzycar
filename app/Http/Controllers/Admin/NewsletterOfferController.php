<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterOffer;
use Illuminate\Http\Request;

class NewsletterOfferController extends Controller
{
    public function index()
    {
        $offers = NewsletterOffer::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.v2.newsletter.index', compact('offers'));
    }

    public function create()
    {
        return view('admin.v2.newsletter.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|string|max:10',
            'kms' => 'required|string|max:50',
            'price' => 'required|string|max:50',
            'savings' => 'required|string|max:50',
            'equipamentos' => 'nullable|string',
            'combustivel' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'published_at' => 'nullable|date',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        NewsletterOffer::create($validated);

        return redirect()->route('admin.v2.newsletter.index')
            ->with('success', 'Oferta criada com sucesso.');
    }

    public function edit($id)
    {
        $offer = NewsletterOffer::findOrFail($id);
        return view('admin.v2.newsletter.form', compact('offer'));
    }

    public function update(Request $request, $id)
    {
        $offer = NewsletterOffer::findOrFail($id);

        $validated = $request->validate([
            'image' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|string|max:10',
            'kms' => 'required|string|max:50',
            'price' => 'required|string|max:50',
            'savings' => 'required|string|max:50',
            'equipamentos' => 'nullable|string',
            'combustivel' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'published_at' => 'nullable|date',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $offer->update($validated);

        return redirect()->route('admin.v2.newsletter.index')
            ->with('success', 'Oferta atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $offer = NewsletterOffer::findOrFail($id);
        $offer->delete();

        return redirect()->route('admin.v2.newsletter.index')
            ->with('success', 'Oferta removida com sucesso.');
    }
}
