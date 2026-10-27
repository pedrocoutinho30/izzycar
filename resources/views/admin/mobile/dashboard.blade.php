@extends('layouts.admin-mobile')

@section('title', 'Início')

@section('content')
    @forelse($grupos as $grupo)
        <section class="mb-3" aria-labelledby="grupo-{{ $loop->index }}">
            <h2 id="grupo-{{ $loop->index }}" class="m-section-title"><i class="bi {{ $grupo['icone'] }}"></i> {{ $grupo['titulo'] }}</h2>
            <div class="row g-2">
                @foreach($grupo['cartoes'] as $cartao)
                    <div class="col-6">
                        <a href="{{ $cartao['href'] }}" class="m-stat">
                            <span class="m-stat__top">
                                <span class="m-stat__value{{ !empty($cartao['alerta']) && $cartao['valor'] > 0 ? ' is-alert' : '' }}">{{ $cartao['valor'] }}</span>
                                <i class="bi {{ $cartao['icone'] }} m-stat__icon" aria-hidden="true"></i>
                            </span>
                            <span class="m-stat__label">{{ $cartao['rotulo'] }}</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </section>
    @empty
        <div class="m-card text-center text-muted py-4">
            <i class="bi bi-info-circle fs-2 d-block mb-2"></i>
            Ainda não tem números para mostrar no seu perfil. Use o menu em baixo para aceder às secções disponíveis.
        </div>
    @endforelse
@endsection
