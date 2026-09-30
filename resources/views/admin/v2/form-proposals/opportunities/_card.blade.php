{{-- Cartão de uma Oportunidade na grelha do Pedido. Espera $opportunity,
     $formProposal e $progress. Os data-* alimentam os filtros/ordenação. --}}
@php $showUrl = route('admin.v2.form-proposals.opportunities.show', [$formProposal->id, $opportunity->id]); @endphp
<div class="col-md-6 col-xl-4" data-opp-card
     data-status="{{ $opportunity->status->value }}"
     data-search="{{ \Illuminate\Support\Str::lower(implode(' ', array_filter([$opportunity->brand, $opportunity->model, $opportunity->version, $opportunity->seller?->name]))) }}"
     data-price="{{ $opportunity->price ?? '' }}"
     data-year="{{ $opportunity->year ?? '' }}"
     data-mileage="{{ $opportunity->mileage ?? '' }}"
     data-created="{{ $opportunity->created_at->timestamp }}">
    <div class="opp-card opp-accent-{{ $opportunity->status->color() }} opp-card--{{ $opportunity->status->value }}">
        <a href="{{ $showUrl }}" class="opp-card-photo" @if($opportunity->photo_url) style="background-image:url('{{ $opportunity->photo_url }}')" @endif>
            @unless($opportunity->photo_url)<i class="bi bi-car-front"></i>@endunless
        </a>

        <div class="opp-card-body">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <div>
                    <div class="opp-card-title"><a href="{{ $showUrl }}">{{ $opportunity->title }}</a></div>
                    @if($opportunity->version)<div class="opp-card-version">{{ $opportunity->version }}</div>@endif
                </div>
                @include('admin.v2.form-proposals.opportunities._status-dropdown')
            </div>

            <div class="opp-card-specs">
                {{ $opportunity->mileage !== null ? number_format($opportunity->mileage, 0, ',', '.') . ' km' : '— km' }}
                · <strong>{{ $opportunity->formatted_price ?? 'Sem preço' }}</strong>
                @if($opportunity->country)<span class="text-muted"> · {{ $opportunity->country }}</span>@endif
            </div>

            <div class="opp-card-meta">
                <span><i class="bi bi-shop"></i> {{ $opportunity->seller?->name ?? 'Vendedor por indicar' }}</span>
                @if($opportunity->contact_method)
                <span><i class="bi {{ $opportunity->contact_method->icon() }}"></i> {{ $opportunity->contact_method->label() }}</span>
                @endif
                <span><i class="bi bi-clock-history"></i> Último contacto: {{ $opportunity->last_contacted_at?->format('d/m/Y') ?? '—' }}</span>
                @if($opportunity->next_followup_at)
                <span class="{{ $opportunity->next_followup_at->isPast() ? 'text-danger fw-semibold' : '' }}">
                    <i class="bi bi-bell"></i> Follow-up: {{ $opportunity->next_followup_at->format('d/m/Y') }}
                </span>
                @endif
            </div>

            <div class="mt-auto">
                @include('admin.v2.form-proposals.opportunities._progress')
            </div>
        </div>

        <div class="opp-card-footer">
            <a href="{{ $showUrl }}" class="btn btn-sm btn-primary-modern">Abrir oportunidade</a>
            <div class="d-flex gap-1">
                @include('admin.v2.form-proposals.opportunities._proposal-button', ['compact' => true])
                @if($opportunity->listing_url)
                <a href="{{ $opportunity->listing_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" title="Ver anúncio">
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
