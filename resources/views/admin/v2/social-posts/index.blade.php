@extends('layouts.admin-v2')

@section('title', 'Criador de Posts')

@section('content')

@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
['icon' => 'bi bi-magic', 'label' => 'Criador de Posts', 'href' => ''],
],
'title' => 'Criador de Posts',
'subtitle' => 'Posts de recomendação de carros, guardados para reutilizar ou editar.',
'actionHref' => route('admin.v2.social-posts.recommendation'),
'actionLabel' => 'Novo Post'
])

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <i class="bi bi-collection"></i>
            Posts de Recomendação
        </h5>
        <span class="badge bg-secondary rounded-pill">{{ $posts->total() }} total</span>
    </div>

    @forelse($posts as $post)
    <div class="modern-item-card mb-3">
        <div class="modern-item-card-body">
            <div class="d-flex justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3 flex-grow-1">
                    @if($post->image)
                    <img src="{{ Illuminate\Support\Facades\Storage::url($post->image) }}" alt=""
                         style="width:64px; height:64px; object-fit:cover; border-radius:10px;">
                    @else
                    <div style="width:64px; height:64px; border-radius:10px; background:#f1f1f1; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-car-front text-muted"></i>
                    </div>
                    @endif
                    <div>
                        <h6 class="modern-item-title mb-1">{{ $post->brand }} {{ $post->model }}</h6>
                        <div class="modern-item-badges">
                            @if($post->price)
                            <span class="badge bg-success"><i class="bi bi-currency-euro"></i> {{ number_format($post->price, 0, ',', '.') }} €</span>
                            @endif
                            @if($post->savings)
                            <span class="badge bg-info"><i class="bi bi-piggy-bank"></i> Poupa {{ number_format($post->savings, 0, ',', '.') }} €</span>
                            @endif
                            <span class="badge bg-secondary"><i class="bi bi-calendar"></i> {{ $post->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
                <div class="modern-item-actions">
                    <a href="{{ route('admin.v2.social-posts.edit', $post->id) }}"
                       class="btn btn-sm btn-outline-primary" title="Editar / Descarregar">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('admin.v2.social-posts.destroy', $post->id) }}"
                          method="POST" class="d-inline"
                          onsubmit="return confirm('Tem a certeza que deseja eliminar este post?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-5">
        <i class="bi bi-inbox display-1 text-muted"></i>
        <p class="text-muted mt-3">Ainda não guardaste nenhum post.</p>
        <a href="{{ route('admin.v2.social-posts.recommendation') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Criar Primeiro Post
        </a>
    </div>
    @endforelse

    @if($posts->hasPages())
    <div class="modern-card-footer">
        {{ $posts->links() }}
    </div>
    @endif
</div>

@endsection
