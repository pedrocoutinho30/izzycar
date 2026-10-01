O cliente {{ $client?->name ?? '—' }} pediu cotação para outra das opções apresentadas na cotação {{ $proposal->proposal_code }}.

Opção: {{ $car }}
@if($alternative->year)Ano: {{ $alternative->year }}
@endif
@if($alternative->mileage !== null)Quilómetros: {{ number_format($alternative->mileage, 0, ',', '.') }} km
@endif
@if($alternative->formatted_price)Preço do anúncio: {{ $alternative->formatted_price }}
@endif
@if($alternative->listing_url)Anúncio: {{ $alternative->listing_url }}
@endif

Contacto do cliente: {{ implode(' · ', array_filter([$client?->email, $client?->phone])) ?: '—' }}

Oportunidade: {{ route('admin.v2.form-proposals.opportunities.show', [$alternative->form_proposal_id, $alternative->id]) }}
Cotação atual: {{ route('admin.v2.proposals.edit', $proposal->id) }}
