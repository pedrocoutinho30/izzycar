@extends('layouts.admin-v2')

@section('title', 'Gestor de Newsletter')

@section('content')
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-newspaper', 'label' => 'Newsletter', 'href' => '']
    ],
    'title' => 'Gestor de Newsletter',
    'subtitle' => 'Gerir newsletters e ofertas',
    'actionHref' => route('admin.v2.newsletter-management.create'),
    'actionLabel' => 'Nova Newsletter'
])

<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <i class="bi bi-list-ul"></i>
            Newsletters
        </h5>
        <span class="badge bg-secondary rounded-pill">{{ $newsletters->total() }} total</span>
    </div>

    @forelse($newsletters as $newsletter)
        @include('components.admin.item-card', [
            'title' => $newsletter->title,
            'subtitle' => $newsletter->subtitle,
            'badges' => array_filter([
                [
                    'text' => $newsletter->offers_count . ' ofertas',
                    'color' => 'info'
                ],
                $newsletter->sent_at ? [
                    'text' => 'Enviada: ' . $newsletter->sent_at->format('d/m/Y H:i'),
                    'color' => 'success'
                ] : [
                    'text' => 'Rascunho',
                    'color' => 'warning'
                ]
            ]),
            'meta' => array_filter([
                $newsletter->text ? [
                    'icon' => 'bi-text-paragraph',
                    'text' => \Str::limit($newsletter->text, 100)
                ] : null,
                [
                    'icon' => 'bi-calendar3',
                    'text' => 'Criada em ' . $newsletter->created_at->format('d/m/Y')
                ]
            ]),
            'actions' => [
                [
                    'href' => route('admin.v2.newsletter-management.send', $newsletter->id),
                    'icon' => 'bi-send',
                    'label' => 'Enviar Newsletter',
                    'color' => 'warning',
                    'target' => '_blank'
                ],
                [
                    'href' => route('admin.v2.newsletter-management.preview', $newsletter->id),
                    'icon' => 'bi-envelope',
                    'label' => 'Pré-visualizar',
                    'color' => 'success',
                    'target' => '_blank'
                ],
                [
                    'href' => route('admin.v2.newsletter-management.show', $newsletter->id),
                    'icon' => 'bi-eye',
                    'label' => 'Ver Detalhes',
                    'color' => 'info'
                ],
                [
                    'href' => route('admin.v2.newsletter-management.edit', $newsletter->id),
                    'icon' => 'bi-pencil',
                    'label' => 'Editar',
                    'color' => 'primary'
                ],
                [
                    'href' => route('admin.v2.newsletter-management.destroy', $newsletter->id),
                    'icon' => 'bi-trash',
                    'label' => 'Eliminar',
                    'color' => 'danger',
                    'method' => 'delete',
                    'confirm' => 'Tem a certeza que pretende eliminar esta newsletter?'
                ]
            ]
        ])
    @empty
        @include('components.admin.empty-state', [
            'icon' => 'bi-newspaper',
            'title' => 'Sem newsletters',
            'description' => 'Ainda não existem newsletters criadas.',
            'actionUrl' => route('admin.v2.newsletter-management.create'),
            'actionText' => 'Criar primeira newsletter'
        ])
    @endforelse
</div>

@if($newsletters->hasPages())
    <div class="pagination-wrapper">
        {{ $newsletters->links() }}
    </div>
@endif
@endsection
