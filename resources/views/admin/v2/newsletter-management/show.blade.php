@extends('layouts.admin-v2')

@section('title', $newsletter->title)

@section('content')
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-newspaper', 'label' => 'Newsletter', 'href' => route('admin.v2.newsletter-management.index')],
        ['icon' => 'bi bi-eye', 'label' => 'Detalhes', 'href' => '']
    ],
    'title' => $newsletter->title,
    'subtitle' => $newsletter->subtitle
])

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-3 mb-3">
    <div class="col-md-8">
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-info-circle"></i>
                    Informações
                </h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.v2.newsletter-management.preview', $newsletter->id) }}" class="btn btn-sm btn-success-modern" target="_blank">
                        <i class="bi bi-envelope"></i> Pré-visualizar
                    </a>
                    <a href="{{ route('admin.v2.newsletter-management.edit', $newsletter->id) }}" class="btn btn-sm btn-primary-modern">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                </div>
            </div>
            <div class="modern-card-body">
                @if($newsletter->text)
                    <p>{{ $newsletter->text }}</p>
                @else
                    <p class="text-muted">Sem texto adicional</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-gear"></i>
                    Estado
                </h5>
            </div>
            <div class="modern-card-body">
                <div class="mb-2">
                    @if($newsletter->sent_at)
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle"></i> Enviada
                        </span>
                        <p class="small text-muted mt-1">{{ $newsletter->sent_at->format('d/m/Y H:i') }}</p>
                    @else
                        <span class="badge bg-warning">
                            <i class="bi bi-clock-history"></i> Rascunho
                        </span>
                    @endif
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="bi bi-calendar3"></i> Criada em {{ $newsletter->created_at->format('d/m/Y') }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <i class="bi bi-envelope-paper"></i>
            Ofertas ({{ $newsletter->offers->count() }})
        </h5>
        <a href="{{ route('admin.v2.newsletter-management.offers.create', $newsletter->id) }}" class="btn btn-sm btn-success-modern">
            <i class="bi bi-plus-lg"></i> Nova Oferta
        </a>
    </div>

    @forelse($newsletter->offers as $offer)
        @include('components.admin.item-card', [
            'image' => asset('storage/' . $offer->image),
            'title' => $offer->brand . ' ' . $offer->model,
            'subtitle' => 'Ano ' . $offer->year . ' • ' . $offer->kms . ' km',
            'badges' => array_filter([
                [
                    'text' => $offer->price,
                    'color' => 'primary'
                ],
                [
                    'text' => 'Poupança ' . $offer->savings,
                    'color' => 'success'
                ],
                $offer->combustivel ? [
                    'text' => $offer->combustivel,
                    'color' => 'info'
                ] : null,
                [
                    'text' => $offer->is_active ? 'Ativo' : 'Inativo',
                    'color' => $offer->is_active ? 'success' : 'danger'
                ]
            ]),
            'meta' => array_filter([
                $offer->equipamentos ? [
                    'icon' => 'bi-clipboard-check',
                    'text' => $offer->equipamentos
                ] : null,
            ]),
            'actions' => [
                [
                    'href' => route('admin.v2.newsletter-management.offers.edit', [$newsletter->id, $offer->id]),
                    'icon' => 'bi-pencil',
                    'label' => 'Editar',
                    'color' => 'primary'
                ],
                [
                    'href' => route('admin.v2.newsletter-management.offers.destroy', [$newsletter->id, $offer->id]),
                    'icon' => 'bi-trash',
                    'label' => 'Eliminar',
                    'color' => 'danger',
                    'method' => 'delete',
                    'confirm' => 'Tem a certeza que pretende eliminar esta oferta?'
                ]
            ]
        ])
    @empty
        @include('components.admin.empty-state', [
            'icon' => 'bi-envelope-paper',
            'title' => 'Sem ofertas',
            'message' => 'Esta newsletter ainda não tem ofertas associadas.',
            'action' => [
                'href' => route('admin.v2.newsletter-management.offers.create', $newsletter->id),
                'text' => 'Adicionar primeira oferta',
                'icon' => 'plus-lg'
            ]
        ])
    @endforelse
</div>
@endsection
