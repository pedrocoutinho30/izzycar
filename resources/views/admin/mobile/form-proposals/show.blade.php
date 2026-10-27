@extends('layouts.admin-mobile')

@section('title', $formProposal->label)

@inject('phones', 'App\Services\PhoneNumberService')

@php
    // O pedido vive dentro da lead/cliente: voltar para lá.
    $cliente = $formProposal->client;
    $voltar = match (true) {
        $cliente !== null && (bool) $cliente->is_lead => route('admin.mobile.leads.show', $cliente->id),
        $cliente !== null && \Illuminate\Support\Facades\Route::has('admin.mobile.clients.show') => route('admin.mobile.clients.show', $cliente->id),
        default => route('admin.mobile.form-proposals.index'),
    };
    $estadoAtual = $formProposal->status ?? 'novo';
    $estadoCores = ['novo' => 'warning', 'em_analise' => 'info', 'convertido' => 'success', 'rejeitado' => 'danger', 'arquivado' => 'secondary'];
@endphp

@section('header_actions')
    <a href="{{ $voltar }}" class="order-first m-topbar-back" aria-label="Voltar"><i class="bi bi-chevron-left fs-4"></i></a>
@endsection

@section('content')
    {{-- Resumo --}}
    <div class="m-card">
        <div class="d-flex justify-content-between align-items-start gap-2">
            <div class="min-w-0">
                <div class="fw-semibold fs-5 text-break">{{ $formProposal->label }}</div>
                <div class="text-muted small">
                    {{ $cliente?->name ?? $formProposal->name }} · {{ $formProposal->isManual() ? 'Pedido criado no backoffice' : 'Pedido do site' }}
                </div>
            </div>
            <span class="badge bg-{{ $estadoCores[$estadoAtual] ?? 'secondary' }} flex-shrink-0">{{ $statuses[$estadoAtual] ?? $estadoAtual }}</span>
        </div>

        @if($canUpdate)
            <div class="d-grid gap-2 mt-3" id="estado">
                @foreach($manualStatuses as $valor)
                    @continue($valor === $estadoAtual)
                    <form action="{{ route('admin.mobile.form-proposals.status', $formProposal->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="{{ $valor }}">
                        <button type="submit" class="btn btn-outline-{{ $estadoCores[$valor] ?? 'secondary' }} w-100 text-start">Marcar como {{ $statuses[$valor] }}</button>
                    </form>
                @endforeach
                @if($estadoAtual === 'convertido')
                    <div class="small text-muted">"Convertido" é automático: o cliente aceitou uma cotação do pedido.</div>
                @endif
            </div>
        @endif
    </div>

    @include('admin.mobile.form-proposals._fields')

    {{-- Oportunidades --}}
    @if($canViewOpportunities)
        <div class="m-card" id="oportunidades">
            <div class="fw-semibold mb-2">
                <i class="bi bi-car-front me-1"></i> Oportunidades
                <span class="badge bg-secondary rounded-pill ms-1">{{ $formProposal->opportunities->count() }}</span>
            </div>

            @forelse($formProposal->opportunities as $oportunidade)
                @php $progresso = $opportunityProgress[$oportunidade->id]; @endphp
                <div class="m-list-item position-relative">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="fw-semibold min-w-0 text-break">{{ $oportunidade->title }}</div>
                        <span class="badge text-bg-{{ $oportunidade->status->color() }} flex-shrink-0">{{ $oportunidade->status->label() }}</span>
                    </div>
                    <div class="small text-muted">
                        {{ $oportunidade->mileage !== null ? number_format($oportunidade->mileage, 0, ',', '.') . ' km' : '— km' }}
                        · {{ $oportunidade->formatted_price ?? 'Sem preço' }}
                        · {{ $oportunidade->seller?->name ?? 'Vendedor por indicar' }}
                    </div>
                    <div class="small text-muted">Informação: {{ $progresso->confirmed }}/{{ $progresso->total }} confirmados</div>
                    <a href="{{ route('admin.mobile.form-proposals.opportunities.show', [$formProposal->id, $oportunidade->id]) }}" class="stretched-link" aria-label="Abrir oportunidade {{ $oportunidade->title }}"></a>
                </div>
            @empty
                <div class="text-muted small">Ainda sem oportunidades.</div>
            @endforelse

            @if($canUpdate)
                <details class="mt-3" @if($errors->any() && old('_form') === 'opportunity-create') open @endif>
                    <summary class="btn btn-primary w-100" style="list-style:none"><i class="bi bi-plus-lg me-1"></i> Nova oportunidade</summary>
                    <form action="{{ route('admin.mobile.form-proposals.opportunities.store', $formProposal->id) }}" method="POST" enctype="multipart/form-data" class="d-grid gap-3 mt-3">
                        @csrf
                        <input type="hidden" name="_form" value="opportunity-create">
                        @include('admin.mobile.form-proposals._brand-model', [
                            'brand' => old('brand', $formProposal->brand),
                            'model' => old('model', $formProposal->model),
                            'required' => true,
                            'idPrefix' => 'm-opp',
                        ])
                        <div>
                            <label class="form-label fw-semibold" for="m-opp-year">Ano</label>
                            <input type="number" id="m-opp-year" name="year" value="{{ old('year') }}" class="form-control" min="1950" max="{{ now()->year + 1 }}" inputmode="numeric">
                        </div>
                        <div>
                            <label class="form-label fw-semibold" for="m-opp-mileage">Quilómetros</label>
                            <input type="number" id="m-opp-mileage" name="mileage" value="{{ old('mileage') }}" class="form-control" min="0" step="1" inputmode="numeric">
                        </div>
                        <div>
                            <label class="form-label fw-semibold" for="m-opp-price">Preço (€)</label>
                            <input type="number" id="m-opp-price" name="price" value="{{ old('price') }}" class="form-control" min="0" step="0.01" inputmode="decimal">
                        </div>
                        <div>
                            <label class="form-label fw-semibold" for="m-opp-fuel">Combustível</label>
                            <select id="m-opp-fuel" name="fuel" class="form-select">
                                <option value="">—</option>
                                @foreach(\App\Enums\VehicleFuel::options() as $valor => $label)
                                    <option value="{{ $valor }}" @selected(old('fuel', \App\Enums\VehicleFuel::fromLoose($formProposal->fuel)?->value) === $valor)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold" for="m-opp-url">URL do anúncio</label>
                            <input type="url" id="m-opp-url" name="listing_url" value="{{ old('listing_url') }}" class="form-control" inputmode="url" autocapitalize="none" placeholder="https://...">
                        </div>
                        <div>
                            <label class="form-label fw-semibold" for="m-opp-photo">Foto</label>
                            <input type="file" id="m-opp-photo" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg fw-semibold">Criar oportunidade</button>
                    </form>
                </details>
            @endif
        </div>
    @endif

    <div class="d-grid gap-2 mt-3">
        @canroute('admin.v2.form-proposals.show')
        <a href="{{ route('admin.v2.form-proposals.show', $formProposal->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-display me-1"></i> Abrir no desktop
        </a>
        @endcanroute
    </div>
@endsection
