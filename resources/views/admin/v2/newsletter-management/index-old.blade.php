@extends('layouts.admin-v2')

@section('title', 'Gestão de Newsletters')

@section('content')
<div class="page-header">
    <div class="header-content">
        <h1 class="page-title">
            <i class="bi bi-newspaper"></i>
            Gestão de Newsletters
        </h1>
        <p class="page-subtitle">Crie e envie newsletters com ofertas destacadas</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('admin.v2.newsletter-management.create') }}" class="btn-primary-modern">
            <i class="bi bi-plus-lg"></i>
            <span>Nova Newsletter</span>
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="content-grid">
    @forelse($newsletters as $newsletter)
        <div class="item-card">
            <div class="item-header">
                <div class="item-info">
                    <h3 class="item-title">{{ $newsletter->title }}</h3>
                    @if($newsletter->subtitle)
                        <p class="item-subtitle text-muted mb-2">{{ $newsletter->subtitle }}</p>
                    @endif
                    @if($newsletter->text)
                        <p class="item-description">{{ Str::limit($newsletter->text, 120) }}</p>
                    @endif
                </div>
            </div>

            <div class="item-meta">
                <div class="meta-badges">
                    @if($newsletter->offer1)
                        <span class="badge-success">
                            <i class="bi bi-1-circle"></i> {{ $newsletter->offer1->brand }} {{ $newsletter->offer1->model }}
                        </span>
                    @endif
                    @if($newsletter->offer2)
                        <span class="badge-success">
                            <i class="bi bi-2-circle"></i> {{ $newsletter->offer2->brand }} {{ $newsletter->offer2->model }}
                        </span>
                    @endif
                    @if($newsletter->offer3)
                        <span class="badge-success">
                            <i class="bi bi-3-circle"></i> {{ $newsletter->offer3->brand }} {{ $newsletter->offer3->model }}
                        </span>
                    @endif
                </div>
                <div class="meta-info">
                    @if($newsletter->sent_at)
                        <span class="badge-info">
                            <i class="bi bi-check-circle"></i> Enviada: {{ $newsletter->sent_at->format('d/m/Y H:i') }}
                        </span>
                    @else
                        <span class="badge-warning">
                            <i class="bi bi-clock-history"></i> Rascunho
                        </span>
                    @endif
                    <span class="meta-date">
                        <i class="bi bi-calendar3"></i>
                        {{ $newsletter->created_at->format('d/m/Y') }}
                    </span>
                </div>
            </div>

            <div class="item-actions">
                <a href="{{ route('admin.v2.newsletter-management.edit', $newsletter->id) }}" class="btn-action btn-edit" title="Editar">
                    <i class="bi bi-pencil"></i>
                </a>
                <form action="{{ route('admin.v2.newsletter-management.destroy', $newsletter->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja eliminar esta newsletter?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-action btn-delete" title="Eliminar">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="empty-state">
            <div class="empty-icon">
                <i class="bi bi-newspaper"></i>
            </div>
            <h3 class="empty-title">Nenhuma newsletter criada</h3>
            <p class="empty-text">Comece por criar a sua primeira newsletter com ofertas destacadas.</p>
            <a href="{{ route('admin.v2.newsletter-management.create') }}" class="btn-primary-modern">
                <i class="bi bi-plus-lg"></i>
                <span>Criar Newsletter</span>
            </a>
        </div>
    @endforelse
</div>

@if($newsletters->hasPages())
    <div class="pagination-wrapper">
        {{ $newsletters->links() }}
    </div>
@endif
@endsection
