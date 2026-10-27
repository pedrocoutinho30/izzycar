@extends('layouts.admin-mobile')

@section('title', 'Pesquisa')

@section('content')
    <div class="m-sticky-filters pb-3">
        <form method="GET" action="{{ route('admin.mobile.search') }}" role="search">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="search" name="q" value="{{ $term }}" class="form-control" @if($term === '') autofocus @endif
                       placeholder="Nome, email, telefone, marca ou código" aria-label="Pesquisar" enterkeyhint="search">
            </div>
        </form>
    </div>

    @if($term === '')
        <div class="m-card text-center text-muted py-4">
            <i class="bi bi-search d-block fs-2 mb-1"></i>
            Pesquise leads, clientes, cotações e pedidos de formulário.
        </div>
    @elseif(mb_strlen($term) < $minLength)
        <div class="m-card text-center text-muted py-4">Escreva pelo menos {{ $minLength }} caracteres.</div>
    @elseif(empty($groups))
        <div class="m-card text-center text-muted py-4">
            <i class="bi bi-search d-block fs-2 mb-1"></i>
            Nenhum resultado para «{{ $term }}».
        </div>
    @else
        @foreach($groups as $group)
            <section class="m-card" aria-label="{{ $group['label'] }}">
                <h2 class="h6 mb-1">{{ $group['label'] }} <span class="badge bg-secondary">{{ count($group['items']) }}</span></h2>
                @foreach($group['items'] as $item)
                    <a href="{{ $item['url'] }}" class="m-list-item m-search-item">
                        <span class="min-w-0">
                            <strong class="d-block m-lead-card__name">{{ $item['title'] }}</strong>
                            @if($item['subtitle'] !== '')
                                <span class="d-block text-muted small text-truncate">{{ $item['subtitle'] }}</span>
                            @endif
                        </span>
                        <i class="bi bi-chevron-right text-muted ms-auto flex-shrink-0"></i>
                    </a>
                @endforeach
            </section>
        @endforeach
    @endif
@endsection

@push('styles')
<style>
    .m-search-item { display: flex; align-items: center; gap: 8px; min-height: 48px; color: inherit; text-decoration: none; }
</style>
@endpush
