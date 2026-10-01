@extends('layouts.admin-v2')

@section('title', 'Pedido de importação — ' . $formProposal->label)

@section('content')


    <!-- Page Header -->
@php
    // O pedido vive dentro da lead/cliente.
    $requestClient = $formProposal->client;
    $clientCrumb = match (true) {
        $requestClient === null => ['icon' => 'bi bi-file-earmark-text', 'label' => 'Pedidos do site', 'href' => route('admin.v2.form-proposals.index')],
        (bool) $requestClient->is_lead => ['icon' => 'bi bi-funnel', 'label' => 'Lead: ' . $requestClient->name, 'href' => route('admin.v2.leads.show', $requestClient->id) . '#pedidos'],
        default => ['icon' => 'bi bi-person', 'label' => 'Cliente: ' . $requestClient->name, 'href' => route('admin.v2.clients.show', $requestClient->id) . '#pedidos'],
    };
    $wanted = trim(implode(' ', array_filter([$formProposal->brand, $formProposal->model, $formProposal->version])));
@endphp
@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
$clientCrumb,
['icon' => '', 'label' => $formProposal->label]
],
'title' => $formProposal->label,
'subtitle' => implode(' · ', array_filter([
    $requestClient?->name ?? $formProposal->name,
    $formProposal->isManual() ? 'Pedido criado no backoffice' : 'Pedido do site',
    filled($formProposal->title) ? ($wanted ?: null) : null,
])),
'actionHref' => $formProposal->proposal_id ? route('admin.v2.proposals.edit', $formProposal->proposal_id) : route('admin.v2.proposals.createFromForm', $formProposal->id),
'actionLabel' => $formProposal->proposal_id ? 'Ver Cotação' : 'Criar Cotação'
])


    <div class="row g-4">
        <!-- COLUNA PRINCIPAL -->
        <div class="col-lg-8">
            <!-- Informações do Cliente -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <h3><i class="bi bi-person"></i> Dados do Cliente</h3>
                </div>
                <div class="detail-card-body">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Nome</span>
                            <span class="detail-value">{{ $formProposal->name }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Email</span>
                            <span class="detail-value">
                                @if($formProposal->email)
                                    <a href="mailto:{{ $formProposal->email }}">{{ $formProposal->email }}</a>
                                @else
                                    <span class="text-muted">Não fornecido</span>
                                @endif
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Telefone</span>
                            <span class="detail-value">
                                @if($formProposal->phone)
                                    <a href="tel:{{ $formProposal->phone }}">{{ $formProposal->phone }}</a>
                                @else
                                    <span class="text-muted">Não fornecido</span>
                                @endif
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Origem</span>
                            <span class="detail-value">
                                @if($formProposal->isManual())
                                    Criado no backoffice
                                @else
                                    Site{{ $formProposal->source ? ' · ' . $formProposal->source : '' }}
                                @endif
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Tipo de Pagamento</span>
                            <span class="detail-value">
                                @php
                                    $paymentLabels = [
                                        'pronto_pagamento' => 'Pronto pagamento',
                                        'financiamento' => 'Financiamento'
                                    ];
                                @endphp
                                {{ $paymentLabels[$formProposal->payment_type] ?? '-' }}
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Tempo Expectável de Compra</span>
                            <span class="detail-value">
                                @php
                                    $purchaseLabels = [
                                        'imediato' => 'Imediato (até 30 dias)',
                                        '1_3_meses' => '1-3 meses',
                                        '3_6_meses' => '3-6 meses',
                                        'pesquisar' => 'Apenas a pesquisar'
                                    ];
                                @endphp
                                {{ $purchaseLabels[$formProposal->estimated_purchase_date] ?? '-' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Especificações do Veículo -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <h3><i class="bi bi-car-front"></i> Especificações Pretendidas</h3>
                </div>
                <div class="detail-card-body">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Marca</span>
                            <span class="detail-value">{{ $formProposal->brand ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Modelo</span>
                            <span class="detail-value">{{ $formProposal->model ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Combustível</span>
                            <span class="detail-value">{{ $formProposal->fuel ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Caixa</span>
                            <span class="detail-value">{{ $formProposal->gearbox ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Ano Mínimo</span>
                            <span class="detail-value">{{ $formProposal->year_min ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">KM Máximo</span>
                            <span class="detail-value">{{ $formProposal->km_max ? number_format($formProposal->km_max, 0, ',', '.') . ' km' : '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Cor</span>
                            <span class="detail-value">{{ $formProposal->color ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Orçamento</span>
                            <span class="detail-value">
                                @if($formProposal->budget)
                                    <strong class="text-primary">{{ number_format($formProposal->budget, 0, ',', '.') }}€</strong>
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                    </div>

                    @if($formProposal->extras)
                        <div class="mt-3">
                            <span class="detail-label">Extras Pretendidos</span>
                            <p class="detail-value">{{ $formProposal->extras }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Anúncio Identificado -->
            @if($formProposal->ad_option === 'sim' && $formProposal->ad_links)
                <div class="detail-card">
                    <div class="detail-card-header">
                        <h3><i class="bi bi-link-45deg"></i> Anúncio Identificado</h3>
                    </div>
                    <div class="detail-card-body">
                        <div class="detail-item">
                            <span class="detail-label">Links do Anúncio</span>
                            <div class="detail-value">
                                @foreach(explode("\n", $formProposal->ad_links) as $link)
                                    @if(trim($link))
                                        <div class="mb-2">
                                            <a href="{{ trim($link) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-box-arrow-up-right"></i> {{ trim($link) }}
                                            </a>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Retoma -->
            @if($formProposal->retoma_option === 'sim')
                <div class="detail-card">
                    <div class="detail-card-header">
                        <h3><i class="bi bi-arrow-left-right"></i> Retoma</h3>
                    </div>
                    <div class="detail-card-body">
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Marca</span>
                                <span class="detail-value">{{ $formProposal->retoma_brand ?? '-' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Modelo</span>
                                <span class="detail-value">{{ $formProposal->retoma_model ?? '-' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Ano</span>
                                <span class="detail-value">{{ $formProposal->retoma_year ?? '-' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">KM</span>
                                <span class="detail-value">{{ $formProposal->retoma_km ? number_format($formProposal->retoma_km, 0, ',', '.') . ' km' : '-' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Combustível</span>
                                <span class="detail-value">{{ $formProposal->retoma_fuel ?? '-' }}</span>
                            </div>
                        </div>

                        @if($formProposal->retoma_info)
                            <div class="mt-3">
                                <span class="detail-label">Mais Informações</span>
                                <p class="detail-value">{{ $formProposal->retoma_info }}</p>
                            </div>
                        @endif

                        @if(!empty($formProposal->retoma_photos))
                            <div class="mt-3">
                                <span class="detail-label">Fotos</span>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    @foreach($formProposal->retoma_photos as $photo)
                                        <a href="{{ Storage::disk('public')->url($photo) }}" target="_blank">
                                            <img src="{{ Storage::disk('public')->url($photo) }}" alt="Foto retoma"
                                                 style="width:90px;height:90px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb;">
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Mensagem -->
            @if($formProposal->message)
                <div class="detail-card">
                    <div class="detail-card-header">
                        <h3><i class="bi bi-chat-text"></i> Mensagem</h3>
                    </div>
                    <div class="detail-card-body">
                        <p class="detail-value">{{ $formProposal->message }}</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- SIDEBAR -->
        <div class="col-lg-4">
            <!-- Estado -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <h3><i class="bi bi-flag"></i> Estado</h3>
                </div>
                <div class="detail-card-body">
                    <form action="{{ route('admin.v2.form-proposals.update-status', $formProposal->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        
                        <select name="status" class="form-select mb-3" onchange="this.form.submit()">
                            <option value="novo" {{ ($formProposal->status ?? 'novo') === 'novo' ? 'selected' : '' }}>Novo</option>
                            <option value="em_analise" {{ ($formProposal->status ?? '') === 'em_analise' ? 'selected' : '' }}>Em Análise</option>
                            {{-- "Convertido" é automático (cotação aceite): só aparece quando é o estado atual. --}}
                            @if(($formProposal->status ?? '') === 'convertido')
                            <option value="convertido" selected disabled>Convertido (cotação aceite)</option>
                            @endif
                            <option value="rejeitado" {{ ($formProposal->status ?? '') === 'rejeitado' ? 'selected' : '' }}>Rejeitado</option>
                            <option value="arquivado" {{ ($formProposal->status ?? '') === 'arquivado' ? 'selected' : '' }}>Arquivado</option>
                        </select>
                    </form>

                    @php
                        $statusColors = [
                            'novo' => 'danger',
                            'em_analise' => 'warning',
                            'convertido' => 'success',
                            'rejeitado' => 'danger',
                            'arquivado' => 'secondary'
                        ];
                        $currentStatus = $formProposal->status ?? 'novo';
                    @endphp
                    
                    <div class="alert alert-{{ $statusColors[$currentStatus] ?? 'secondary' }} mb-0">
                        <small>Estado atual do pedido</small>
                    </div>

                    @canroute('admin.v2.form-proposals.update')
                    <button type="button" class="btn btn-secondary-modern w-100 mt-3" data-bs-toggle="modal" data-bs-target="#editRequestModal">
                        <i class="bi bi-pencil"></i> Editar pedido
                    </button>
                    @endcanroute
                </div>
            </div>

            <!-- Informações Temporais -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <h3><i class="bi bi-clock"></i> Informações</h3>
                </div>
                <div class="detail-card-body">
                    <div class="detail-item">
                        <span class="detail-label">{{ $formProposal->isManual() ? 'Criado em' : 'Recebido em' }}</span>
                        <span class="detail-value">{{ $formProposal->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Há quanto tempo</span>
                        <span class="detail-value">{{ $formProposal->created_at->diffForHumans() }}</span>
                    </div>
                    @if($formProposal->creator)
                    <div class="detail-item">
                        <span class="detail-label">Criado por</span>
                        <span class="detail-value">{{ $formProposal->creator->name }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Ações Rápidas -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <h3><i class="bi bi-lightning"></i> Ações Rápidas</h3>
                </div>
                <div class="detail-card-body">
                    <div class="d-grid gap-2">
                        @if($formProposal->email)
                            <a href="mailto:{{ $formProposal->email }}" class="btn btn-outline-primary">
                                <i class="bi bi-envelope"></i> Enviar Email
                            </a>
                        @endif
                        @if($formProposal->phone)
                            <a href="tel:{{ $formProposal->phone }}" class="btn btn-outline-success">
                                <i class="bi bi-telephone"></i> Ligar
                            </a>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $formProposal->phone) }}" target="_blank" class="btn btn-outline-success">
                                <i class="bi bi-whatsapp"></i> WhatsApp
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">@include('admin.v2.form-proposals.opportunities._flash')</div>
    @include('admin.v2.form-proposals.partials.opportunities')

    @canroute('admin.v2.form-proposals.update')
    @php $openEdit = $errors->any() && old('_form') === 'edit-request'; @endphp
    <div class="modal fade" id="editRequestModal" tabindex="-1" aria-hidden="true" @if($openEdit) data-edit-request-open @endif>
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.v2.form-proposals.update', $formProposal->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_form" value="edit-request">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar pedido</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @include('admin.v2.form-proposals._request-fields', ['formProposal' => $formProposal, 'useOld' => $openEdit])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary-modern"><i class="bi bi-check"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @push('scripts')
    <script>document.querySelectorAll('[data-edit-request-open]').forEach(modal => new bootstrap.Modal(modal).show());</script>
    @endpush
    @endcanroute
</div>

<style>
.detail-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
    overflow: hidden;
}

.detail-card-header {
    background: linear-gradient(135deg, var(--admin-primary), var(--admin-primary-dark));
    color: #fff;
    padding: 1rem 1.5rem;
}

.detail-card-header h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.detail-card-body {
    padding: 1.5rem;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.detail-label {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #666;
    letter-spacing: 0.5px;
}

.detail-value {
    font-size: 0.95rem;
    color: #2c3e50;
    font-weight: 500;
}

.detail-value a {
    color: var(--admin-primary);
    text-decoration: none;
}

.detail-value a:hover {
    text-decoration: underline;
}
</style>
@endsection
