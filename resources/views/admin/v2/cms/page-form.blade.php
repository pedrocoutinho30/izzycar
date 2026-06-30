@extends('layouts.admin-v2')
@section('title', 'Nova Página CMS')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door',                 'label' => 'Dashboard',          'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-layout-text-window-reverse', 'label' => 'Gestor de Conteúdo', 'href' => route('admin.v2.cms.index')],
        ['icon' => '', 'label' => 'Nova Página'],
    ],
    'title'       => 'Nova Página',
    'subtitle'    => 'Crie uma página para gerir os seus blocos de conteúdo.',
    'actionHref'  => '',
    'actionLabel' => '',
])

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title"><i class="bi bi-file-plus"></i> Dados da Página</h5>
            </div>
            <form method="POST" action="{{ route('admin.v2.cms.store-page') }}" class="p-3">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Nome interno <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                           placeholder="ex: Homepage, Importação, Legalização" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Slug <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text text-muted">/</span>
                        <input type="text" name="slug" id="slugField" class="form-control font-monospace"
                               value="{{ old('slug') }}" placeholder="home, importacao, legalizacao" required>
                    </div>
                    <div class="form-text">Identificador único, sem espaços. Usado internamente para carregar esta página no frontend.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ordem</label>
                    <input type="number" name="order" class="form-control" value="{{ old('order', 0) }}" min="0">
                </div>
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" name="active" value="1" id="pageActive" checked>
                    <label class="form-check-label" for="pageActive">Página activa</label>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary-modern">
                        <i class="bi bi-plus-lg me-1"></i> Criar Página
                    </button>
                    <a href="{{ route('admin.v2.cms.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Auto-gera slug a partir do nome
document.querySelector('[name=name]').addEventListener('input', function() {
    const slug = this.value.toLowerCase()
        .normalize('NFD').replace(/[̀-ͯ]/g, '')
        .replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    document.getElementById('slugField').value = slug;
});
</script>
@endpush
