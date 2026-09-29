{{-- "Criar cotação" / "Cotação #X" de uma Oportunidade (uma por oportunidade).
     Espera $opportunity; $compact = só ícone + número (cartão). --}}
@if($opportunity->proposal_id)
<a href="{{ route('admin.v2.proposals.edit', $opportunity->proposal_id) }}" class="btn btn-sm btn-outline-success" title="Ver cotação #{{ $opportunity->proposal_id }}">
    <i class="bi bi-file-earmark-check"></i> {{ $compact ? '#' . $opportunity->proposal_id : 'Ver cotação #' . $opportunity->proposal_id }}
</a>
@else
<a href="{{ route('admin.v2.proposals.createFromOpportunity', $opportunity->id) }}" class="btn btn-sm {{ $compact ? 'btn-outline-secondary' : 'btn-primary-modern' }}" title="Criar cotação a partir desta oportunidade">
    <i class="bi bi-file-earmark-plus"></i>@unless($compact) Criar cotação @endunless
</a>
@endif
