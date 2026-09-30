<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SellerRequest;
use App\Models\Seller;
use App\Services\SellerDuplicateDetector;
use App\Services\SellerService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Vendedores (empresas/stands) com quem a IzzyCar trabalha: listagem, ficha
 * com contactos/veículos/oportunidades, e os endpoints JSON usados pelo
 * seletor de vendedor nas Oportunidades e nos Veículos.
 */
class SellerController extends Controller
{
    public function __construct(private SellerService $sellers)
    {
    }

    public function index(Request $request)
    {
        $sellers = Seller::query()
            ->with('primaryContact')
            ->withCount(['contacts', 'vehicles', 'opportunities'])
            ->search($request->input('search'))
            ->when($request->input('status') === 'ativos', fn ($q) => $q->where('is_active', true))
            ->when($request->input('status') === 'inativos', fn ($q) => $q->where('is_active', false))
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            ['title' => 'Total de Vendedores', 'value' => Seller::count(), 'color' => 'primary', 'icon' => 'bi-shop'],
            ['title' => 'Ativos', 'value' => Seller::active()->count(), 'color' => 'success', 'icon' => 'bi-check-circle'],
            ['title' => 'Com veículos vendidos', 'value' => Seller::has('vehicles')->count(), 'color' => 'info', 'icon' => 'bi-car-front'],
        ];

        return view('admin.v2.sellers.index', compact('sellers', 'stats'));
    }

    public function create()
    {
        return view('admin.v2.sellers.form', ['seller' => null]);
    }

    /**
     * Também usado pelo modal "Criar vendedor" das Oportunidades (JSON).
     */
    public function store(SellerRequest $request)
    {
        $existing = $request->existingSellerId() ? Seller::findOrFail($request->existingSellerId()) : null;
        $contactData = $request->contactData();

        $matches = $existing
            ? $this->sellers->checkContact($contactData, $existing->id, $existing->country)
            : $this->sellers->checkContact($contactData ?? [], null, $request->validated('country'), null, $request->domainList());

        $this->sellers->ensureNoUnconfirmedMatches($matches, $request->matchesConfirmed());

        if ($existing) {
            $seller = $existing;
            $contact = $this->sellers->addContact($seller, $contactData);
            $message = "Contacto \"{$contact->name}\" adicionado a {$seller->name}.";
        } else {
            $seller = $this->sellers->create($request->sellerData(), $contactData, $request->user());
            $contact = $seller->contacts()->first();
            $message = "Vendedor \"{$seller->name}\" criado.";
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'seller' => $this->pickerSeller($seller->fresh('primaryContact')),
                'contact' => $contact?->toPickerArray(),
            ]);
        }

        return redirect()->route('admin.v2.sellers.show', $seller->id)->with('success', $message);
    }

    public function show(Seller $seller)
    {
        $seller->load([
            'domains',
            'contacts',
            'creator',
            'vehicles' => fn ($q) => $q->with('sellerContact')->orderByDesc('purchase_date')->orderByDesc('id'),
            'opportunities' => fn ($q) => $q->with(['formProposal', 'sellerContact'])->latest(),
        ]);

        return view('admin.v2.sellers.show', [
            'seller' => $seller,
            'canDelete' => !$seller->hasHistory(),
        ]);
    }

    public function edit(Seller $seller)
    {
        $seller->load('domains');

        return view('admin.v2.sellers.form', compact('seller'));
    }

    public function update(SellerRequest $request, Seller $seller)
    {
        $matches = $this->sellers->checkContact([], $seller->id, null, null, $request->domainList());
        $this->sellers->ensureNoUnconfirmedMatches($matches, $request->matchesConfirmed());

        $this->sellers->update($seller, $request->sellerData());

        return redirect()->route('admin.v2.sellers.show', $seller->id)->with('success', 'Vendedor atualizado.');
    }

    public function toggleActive(Seller $seller)
    {
        $this->sellers->setActive($seller, !$seller->is_active);

        return back()->with('success', $seller->is_active ? 'Vendedor reativado.' : 'Vendedor desativado. Continua visível no histórico.');
    }

    public function destroy(Seller $seller)
    {
        try {
            $this->sellers->delete($seller);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('admin.v2.sellers.index')->with('success', 'Vendedor eliminado.');
    }

    /** Pesquisa para o seletor (Tom Select) — só vendedores ativos. */
    public function search(Request $request)
    {
        $sellers = Seller::active()
            ->with('primaryContact')
            ->search($request->input('q'))
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json($sellers->map(fn (Seller $seller) => $this->pickerSeller($seller))->values());
    }

    public function contacts(Seller $seller)
    {
        return response()->json($seller->activeContacts->map->toPickerArray()->values());
    }

    public function checkDuplicates(Request $request, SellerDuplicateDetector $detector)
    {
        $data = $request->validate([
            'email' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'domains' => 'nullable|string|max:1000',
            'country' => 'nullable|string|max:2',
            'seller_id' => 'nullable|integer',
            'contact_id' => 'nullable|integer',
        ]);

        $matches = $detector->check(
            $data,
            isset($data['seller_id']) ? (int) $data['seller_id'] : null,
            isset($data['contact_id']) ? (int) $data['contact_id'] : null,
        );

        return response()->json($matches->toArray());
    }

    private function pickerSeller(Seller $seller): array
    {
        return [
            'id' => $seller->id,
            'name' => $seller->name,
            'country' => $seller->country,
            'country_label' => $seller->country_label,
            'primary_contact' => $seller->primaryContact?->name,
            'url' => route('admin.v2.sellers.show', $seller->id),
        ];
    }
}
