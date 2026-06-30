@extends('layouts.admin-v2')
@section('title', 'Gestor de Conteúdo')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs'  => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => '', 'label' => 'Gestor de Conteúdo']
    ],
    'title'        => 'Gestor de Conteúdo',
    'subtitle'     => 'Edite os textos, títulos e secções que aparecem no site.',
    'actionHref'   => route('admin.v2.cms.create-page'),
    'actionLabel'  => 'Nova Página',
])

<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title"><i class="bi bi-layout-text-window-reverse"></i> Páginas</h5>
        <span class="badge bg-secondary rounded-pill">{{ $pages->count() }}</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Página</th>
                    <th style="width:80px" class="text-center">Blocos</th>
                    <th style="width:100px" class="text-center">Estado</th>
                    <th style="width:100px"></th>
                </tr>
            </thead>
            <tbody>
            @forelse($pages as $page)
            <tr>
                <td>
                    <div class="fw-semibold">{{ $page->name }}</div>
                    <div class="small text-muted font-monospace">/{{ $page->slug }}</div>
                </td>
                <td class="text-center">
                    <span class="badge bg-secondary rounded-pill">{{ $page->blocks_count }}</span>
                </td>
                <td class="text-center">
                    @if($page->active)
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Activa</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Inactiva</span>
                    @endif
                </td>
                <td class="text-end">
                    <a href="{{ route('admin.v2.cms.page', $page->id) }}"
                       class="btn btn-sm btn-primary-modern">
                        <i class="bi bi-pencil me-1"></i> Editar
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center text-muted py-5">
                    <i class="bi bi-layout-text-window fs-2 d-block mb-2"></i>
                    Nenhuma página criada. <a href="{{ route('admin.v2.cms.create-page') }}">Criar primeira página</a>
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
