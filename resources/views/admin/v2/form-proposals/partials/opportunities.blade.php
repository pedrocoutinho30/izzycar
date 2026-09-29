{{-- Secção "Oportunidades" na página do Pedido de Importação: grelha de
     cartões com filtros por estado, pesquisa e ordenação (no browser — um
     pedido tem no máximo algumas dezenas de oportunidades), e modal de
     criação rápida. Espera $formProposal e $opportunityProgress. --}}
@include('admin.v2.form-proposals.opportunities._styles')

@php
    $opportunities = $formProposal->opportunities;
    $statusCounts = $opportunities->countBy(fn ($o) => $o->status->value);
    $openCreateModal = $errors->any() && old('_form') === 'opportunity-create';
@endphp

<div class="modern-card" id="oportunidades">
    <div class="modern-card-header flex-wrap gap-2">
        <h5 class="modern-card-title mb-0">
            <i class="bi bi-car-front"></i> Oportunidades
            <span class="badge bg-light text-dark fw-normal ms-1">{{ $opportunities->count() }}</span>
        </h5>
        <button type="button" class="btn btn-sm btn-primary-modern" data-bs-toggle="modal" data-bs-target="#opportunityCreateModal">
            <i class="bi bi-plus"></i> Nova oportunidade
        </button>
    </div>

    @if($opportunities->isEmpty())
        @include('components.admin.empty-state', [
            'icon' => 'bi-car-front',
            'title' => 'Ainda sem oportunidades',
            'description' => 'Adicione os carros/anúncios que está a analisar para este pedido.'
        ])
    @else
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3" data-opp-filters>
            <button type="button" class="opp-filter-chip active" data-filter-status="">Todas <span class="count">{{ $opportunities->count() }}</span></button>
            @foreach(\App\Enums\OpportunityStatus::cases() as $status)
                @if($statusCounts->get($status->value))
                <button type="button" class="opp-filter-chip" data-filter-status="{{ $status->value }}">
                    {{ $status->label() }} <span class="count">{{ $statusCounts->get($status->value) }}</span>
                </button>
                @endif
            @endforeach

            <div class="ms-auto d-flex gap-2">
                <input type="search" class="form-control form-control-sm" style="width: 210px" placeholder="Marca, modelo ou vendedor" data-filter-search>
                <select class="form-select form-select-sm" style="width: auto" data-sort>
                    <option value="created:desc">Mais recentes</option>
                    <option value="price:asc">Preço ↑</option>
                    <option value="price:desc">Preço ↓</option>
                    <option value="year:desc">Ano (mais novo)</option>
                    <option value="mileage:asc">Km (menos)</option>
                </select>
            </div>
        </div>

        <div class="row g-3" data-opp-grid>
            @foreach($opportunities as $opportunity)
                @include('admin.v2.form-proposals.opportunities._card', ['progress' => $opportunityProgress[$opportunity->id]])
            @endforeach
        </div>
        <p class="text-muted text-center small mt-3 mb-0 d-none" data-opp-no-results>Nenhuma oportunidade corresponde aos filtros.</p>
    @endif
</div>

{{-- Modal: nova oportunidade (só marca e modelo obrigatórios) --}}
<div class="modal fade" id="opportunityCreateModal" tabindex="-1" aria-hidden="true" @if($openCreateModal) data-open-on-load @endif>
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.v2.form-proposals.opportunities.store', $formProposal->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_form" value="opportunity-create">
                <div class="modal-header">
                    <h5 class="modal-title">Nova oportunidade</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('admin.v2.form-proposals.opportunities._vehicle-fields', [
                        'quick' => true,
                        'opportunity' => null,
                        'defaults' => [
                            'brand' => $formProposal->brand,
                            'model' => $formProposal->model,
                            'fuel' => \App\Enums\VehicleFuel::fromLoose($formProposal->fuel)?->value,
                        ],
                    ])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-modern"><i class="bi bi-plus"></i> Criar oportunidade</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin.v2.form-proposals.opportunities._scripts')
