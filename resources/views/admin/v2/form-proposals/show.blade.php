@extends('layouts.admin-v2')

@section('title', 'Detalhes do Formulário')

@section('content')
<div class="admin-content">
    <!-- HEADER -->
    <div class="content-header">
        <div>
            <h1 class="content-title">Detalhes do Formulário</h1>
            <p class="content-subtitle">Pedido de {{ $formProposal->name }}</p>
        </div>
        <div class="content-actions">
            <a href="{{ route('admin.v2.form-proposals.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

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
                            <span class="detail-value">{{ $formProposal->source ?? 'Website' }}</span>
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
            @if($formProposal->has_trade_in)
                <div class="detail-card">
                    <div class="detail-card-header">
                        <h3><i class="bi bi-arrow-left-right"></i> Retoma</h3>
                    </div>
                    <div class="detail-card-body">
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> Cliente tem interesse em retoma
                        </div>
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
                            <option value="convertido" {{ ($formProposal->status ?? '') === 'convertido' ? 'selected' : '' }}>Convertido</option>
                            <option value="arquivado" {{ ($formProposal->status ?? '') === 'arquivado' ? 'selected' : '' }}>Arquivado</option>
                        </select>
                    </form>

                    @php
                        $statusColors = [
                            'novo' => 'danger',
                            'em_analise' => 'warning',
                            'convertido' => 'success',
                            'arquivado' => 'secondary'
                        ];
                        $currentStatus = $formProposal->status ?? 'novo';
                    @endphp
                    
                    <div class="alert alert-{{ $statusColors[$currentStatus] ?? 'secondary' }} mb-0">
                        <small>Estado atual do pedido</small>
                    </div>
                </div>
            </div>

            <!-- Informações Temporais -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <h3><i class="bi bi-clock"></i> Informações</h3>
                </div>
                <div class="detail-card-body">
                    <div class="detail-item">
                        <span class="detail-label">Recebido em</span>
                        <span class="detail-value">{{ $formProposal->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Há quanto tempo</span>
                        <span class="detail-value">{{ $formProposal->created_at->diffForHumans() }}</span>
                    </div>
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
