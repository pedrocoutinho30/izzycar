@extends('layouts.admin-v2')

@section('title', 'Gestor de Newsletter')

@section('content')
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-envelope-paper', 'label' => 'Newsletter', 'href' => '']
    ],
    'title' => 'Gestor de Newsletter',
    'subtitle' => 'Gerir ofertas para newsletters',
    'actionHref' => route('admin.v2.newsletter.create'),
    'actionLabel' => 'Nova Newsletter'
])

<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <i class="bi bi-list-ul"></i>
            Ofertas
        </h5>
        <span class="badge bg-secondary rounded-pill">{{ $offers->total() }} total</span>
    </div>

    @forelse($offers as $offer)
        @include('components.admin.item-card', [
            'image' => $offer->image,
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
                $offer->published_at ? [
                    'text' => 'Publicação: ' . $offer->published_at->format('d/m/Y'),
                    'color' => 'secondary'
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
                    'href' => route('admin.v2.newsletter.edit', $offer->id),
                    'icon' => 'bi-pencil',
                    'label' => 'Editar',
                    'color' => 'primary'
                ],
                [
                    'href' => route('admin.v2.newsletter.destroy', $offer->id),
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
            'description' => 'Ainda não existem ofertas para newsletter.',
            'actionUrl' => route('admin.v2.newsletter.create'),
            'actionText' => 'Criar primeira oferta'
        ])
    @endforelse
</div>

@if($offers->hasPages())
    <div class="pagination-wrapper">
        {{ $offers->links() }}
    </div>
@endif
@endsection
