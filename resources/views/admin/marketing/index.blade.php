@extends('layouts.admin-v2')

@section('title', 'Marketing Studio')

@section('content')

@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
['icon' => 'bi bi-stars', 'label' => 'Marketing Studio', 'href' => ''],
],
'title' => 'Marketing Studio',
'subtitle' => 'Cria, guarda e exporta publicações para as redes sociais.',
'actionHref' => route('admin.marketing.create'),
'actionLabel' => 'Criar publicação'
])

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('admin.marketing.vehicles.create') }}" class="btn btn-primary"><i class="bi bi-car-front me-1"></i> Publicação de viatura</a>
    <a href="{{ route('admin.marketing.recommendations.create') }}" class="btn btn-outline-primary"><i class="bi bi-hand-thumbs-up me-1"></i> Nova recomendação</a>
    <a href="{{ route('admin.marketing.calendar') }}" class="btn btn-outline-secondary"><i class="bi bi-calendar3 me-1"></i> Calendário</a>
    <a href="{{ route('admin.marketing.dashboard') }}" class="btn btn-outline-secondary"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a>
    <form method="POST" action="{{ route('admin.marketing.import-recommendations') }}" class="ms-auto"
          onsubmit="return confirm('Copiar os posts do Criador de Posts antigo para o Marketing Studio? Os originais não são alterados.')">
        @csrf
        <button class="btn btn-outline-secondary"><i class="bi bi-box-arrow-in-down me-1"></i> Importar do Criador de Posts</button>
    </form>
</div>

<form method="GET" class="row g-2 mb-4">
    <div class="col-md-4"><input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="Pesquisar por título…"></div>
    <div class="col-md-3">
        <select name="type" class="form-select"><option value="">Todos os tipos</option>
            @foreach($types as $t)<option value="{{ $t->value }}" @selected(($filters['type'] ?? '') === $t->value)>{{ $t->label() }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-3">
        <select name="status" class="form-select"><option value="">Todos os estados</option>
            @foreach($statuses as $s)<option value="{{ $s->value }}" @selected(($filters['status'] ?? '') === $s->value)>{{ $s->label() }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-outline-secondary w-100"><i class="bi bi-funnel"></i> Filtrar</button></div>
</form>

<div class="row g-3">
    @forelse($posts as $post)
        @php
            $foto = ($post->data['photos'][0] ?? null);
            $thumb = null;
            if ($foto) {
                $thumb = $post->type === \App\Enums\MarketingPostType::Veiculo
                    ? (isset($foto['id']) ? route('admin.marketing.photos.show', ['photo' => $foto['id'], 'w' => 480]) : null)
                    : route('admin.marketing.post-photos.show', ['post' => $post, 'index' => 0, 'w' => 480]);
            }
        @endphp
        <div class="col-sm-6 col-lg-4 col-xxl-3">
            <div class="card h-100">
                <a href="{{ route('admin.marketing.editor', $post) }}">
                    <div style="aspect-ratio:4/3;background:#262626 {{ $thumb ? "url('{$thumb}') center/cover" : '' }}"></div>
                </a>
                <div class="card-body">
                    <div class="fw-semibold text-truncate">{{ $post->title ?: 'Sem título' }}</div>
                    <div class="mt-1 d-flex flex-wrap gap-1">
                        <span class="badge text-bg-light">{{ $post->type->label() }}</span>
                        <span class="badge text-bg-{{ $post->status->color() }}">{{ $post->status->label() }}</span>
                    </div>
                    <div class="text-muted small mt-2">
                        {{ \App\Marketing\TemplateRegistry::all()[$post->template]->name ?? $post->template }} · {{ $post->format->label() }}<br>
                        @if($post->creator){{ $post->creator->name }} · @endif Atualizado {{ $post->updated_at->format('d/m/Y H:i') }}
                    </div>
                </div>
                <div class="card-footer d-flex gap-2">
                    <a href="{{ route('admin.marketing.editor', $post) }}" class="btn btn-sm btn-primary flex-grow-1"><i class="bi bi-pencil"></i> Editar / exportar</a>
                    <form method="POST" action="{{ route('admin.marketing.duplicate', $post) }}">@csrf<button class="btn btn-sm btn-outline-secondary" title="Duplicar"><i class="bi bi-files"></i></button></form>
                    <form method="POST" action="{{ route('admin.marketing.destroy', $post) }}" onsubmit="return confirm('Eliminar esta publicação?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button></form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            @include('components.admin.empty-state', ['icon' => 'stars', 'title' => 'Ainda não há publicações', 'message' => 'Cria a primeira a partir de uma viatura ou de uma recomendação.'])
        </div>
    @endforelse
</div>

<div class="mt-4">{{ $posts->links() }}</div>

@endsection
