<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SellerContactRequest;
use App\Models\Seller;
use App\Models\SellerContact;
use App\Services\SellerService;

/**
 * Contactos (pessoas) de um Vendedor. O store também serve o modal
 * "Novo contacto" das Oportunidades (JSON).
 */
class SellerContactController extends Controller
{
    public function __construct(private SellerService $sellers)
    {
    }

    public function store(SellerContactRequest $request, Seller $seller)
    {
        $data = $request->contactData();
        $matches = $this->sellers->checkContact($data, $seller->id, $seller->country);
        $this->sellers->ensureNoUnconfirmedMatches($matches, $request->matchesConfirmed());

        $contact = $this->sellers->addContact($seller, $data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Contacto \"{$contact->name}\" adicionado.",
                'contact' => $contact->toPickerArray(),
            ]);
        }

        return redirect()
            ->route('admin.v2.sellers.show', $seller->id)
            ->with('success', "Contacto \"{$contact->name}\" adicionado.")
            ->withFragment('contactos');
    }

    public function update(SellerContactRequest $request, Seller $seller, SellerContact $contact)
    {
        $data = $request->contactData();
        $matches = $this->sellers->checkContact($data, $seller->id, $seller->country, $contact->id);
        $this->sellers->ensureNoUnconfirmedMatches($matches, $request->matchesConfirmed());

        $this->sellers->updateContact($contact, $data);

        return redirect()
            ->route('admin.v2.sellers.show', $seller->id)
            ->with('success', 'Contacto atualizado.')
            ->withFragment('contactos');
    }

    public function setPrimary(Seller $seller, SellerContact $contact)
    {
        $this->sellers->setPrimary($contact);

        return redirect()
            ->route('admin.v2.sellers.show', $seller->id)
            ->with('success', "{$contact->name} é agora o contacto principal.")
            ->withFragment('contactos');
    }

    public function toggleActive(Seller $seller, SellerContact $contact)
    {
        $this->sellers->setContactActive($contact, !$contact->is_active);

        return redirect()
            ->route('admin.v2.sellers.show', $seller->id)
            ->with('success', $contact->is_active ? 'Contacto reativado.' : 'Contacto desativado.')
            ->withFragment('contactos');
    }
}
