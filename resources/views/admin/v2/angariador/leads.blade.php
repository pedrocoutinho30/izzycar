@extends('layouts.admin-v2')

@section('title', 'As Minhas Leads')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Início', 'href' => route('admin.angariador.dashboard')],
        ['icon' => '', 'label' => 'As Minhas Leads'],
    ],
    'title' => 'As Minhas Leads',
    'subtitle' => 'Contactos que registou através do seu código de angariador',
])

<div class="d-flex justify-content-end mb-3 gap-2">
    <button type="button" class="btn btn-primary-modern btn-sm" data-bs-toggle="modal" data-bs-target="#createLeadModal">
        <i class="bi bi-person-plus me-1"></i> Criar Lead
    </button>
    <a href="{{ route('admin.angariador.leads') }}" class="btn btn-primary-modern btn-sm">
        <i class="bi bi-list-ul me-1"></i> Vista de Lista
    </a>
    <a href="{{ route('admin.angariador.leads.kanban') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-kanban me-1"></i> Pipeline Kanban
    </a>
</div>

<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title"><i class="bi bi-funnel"></i> Leads</h5>
        <span class="badge bg-secondary rounded-pill">{{ $leads->count() }} total</span>
    </div>

    @forelse($leads as $lead)
    <div class="lead-card">
        <div class="lead-card__avatar">{{ strtoupper(substr($lead->name, 0, 1)) }}</div>
        <div class="lead-card__info">
            <div class="lead-card__name">{{ $lead->name }}</div>
            <div class="lead-card__meta">
                @if($lead->email)<span><i class="bi bi-envelope"></i> {{ $lead->email }}</span>@endif
                @if($lead->phone)<span><i class="bi bi-telephone"></i> {{ $lead->phone }}</span>@endif
                <span><i class="bi bi-clock"></i> {{ $lead->created_at->diffForHumans() }}</span>
            </div>
        </div>
        <div class="lead-card__badges">
            @if($lead->proposals_count > 0)
                <span class="badge bg-info">Proposta enviada</span>
            @else
                <span class="badge bg-secondary">Em tratamento pela equipa</span>
            @endif
        </div>
        <div class="item-actions">
            <a href="{{ route('admin.angariador.leads.show', $lead->id) }}" class="btn btn-icon btn-primary-modern" title="Ver detalhes">
                <i class="bi bi-eye"></i>
            </a>
        </div>
    </div>
    @empty
    @include('components.admin.empty-state', [
        'icon' => 'bi-funnel',
        'title' => 'Ainda sem leads',
        'message' => 'Partilhe o seu link de angariador para começar a gerar leads.',
    ])
    @endforelse
</div>

{{-- Modal: Criar Lead Manualmente --}}
<div class="modal fade" id="createLeadModal" tabindex="-1" aria-labelledby="createLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.angariador.leads.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createLeadModalLabel"><i class="bi bi-person-plus"></i> Criar Lead Manualmente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nome <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="Nome completo" autofocus>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="email@exemplo.com">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Telefone</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone') }}" placeholder="+351 9XX XXX XXX">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Como chegou</label>
                            <select name="origin" class="form-select @error('origin') is-invalid @enderror">
                                <option value="Manual Angariador" {{ old('origin', 'Manual Angariador') == 'Manual Angariador' ? 'selected' : '' }}>Contacto direto</option>
                                <option value="Telefone" {{ old('origin') == 'Telefone' ? 'selected' : '' }}>Telefone / WhatsApp</option>
                                <option value="Referência" {{ old('origin') == 'Referência' ? 'selected' : '' }}>Referência</option>
                                <option value="Instagram" {{ old('origin') == 'Instagram' ? 'selected' : '' }}>Instagram</option>
                                <option value="Facebook" {{ old('origin') == 'Facebook' ? 'selected' : '' }}>Facebook</option>
                                <option value="Outro" {{ old('origin') == 'Outro' ? 'selected' : '' }}>Outro</option>
                            </select>
                            @error('origin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Estado inicial</label>
                            <select name="lead_status" class="form-select @error('lead_status') is-invalid @enderror">
                                <option value="nova" {{ old('lead_status', 'nova') == 'nova' ? 'selected' : '' }}>Nova</option>
                                <option value="em_contacto" {{ old('lead_status') == 'em_contacto' ? 'selected' : '' }}>Em Contacto</option>
                                <option value="fria" {{ old('lead_status') == 'fria' ? 'selected' : '' }}>Fria</option>
                            </select>
                            @error('lead_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold">Notas / Contexto</label>
                        <textarea name="observation" class="form-control @error('observation') is-invalid @enderror"
                                  rows="3" placeholder="Ex: conhecido pessoal, interessado em importar um SUV até 30.000€.">{{ old('observation') }}</textarea>
                        @error('observation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-modern">
                        <i class="bi bi-person-plus me-1"></i> Criar Lead
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
@if($errors->any())
<script>
document.addEventListener('DOMContentLoaded', function () {
    new bootstrap.Modal(document.getElementById('createLeadModal')).show();
});
</script>
@endif
@endpush

@push('styles')
<style>
.lead-card { display: flex; align-items: center; gap: 1rem; padding: 1rem 1.25rem; border-bottom: 1px solid var(--admin-border); }
.lead-card:last-child { border-bottom: none; }
.lead-card__avatar { width: 42px; height: 42px; flex-shrink: 0; border-radius: 50%; background: linear-gradient(135deg, var(--admin-primary), #990000); color: #fff; font-size: .95rem; font-weight: 700; display: flex; align-items: center; justify-content: center; }
.lead-card__info { flex: 1; min-width: 0; }
.lead-card__name { font-weight: 600; color: #111; font-size: .95rem; }
.lead-card__meta { display: flex; flex-wrap: wrap; gap: .75rem; font-size: .78rem; color: #6c757d; margin-top: .2rem; }
.lead-card__meta span { display: flex; align-items: center; gap: .3rem; }
.lead-card__badges { flex-shrink: 0; }

@media (max-width: 768px) {
    .lead-card { flex-wrap: wrap; }
    .item-actions { width: 100%; justify-content: flex-end; }
}
</style>
@endpush
