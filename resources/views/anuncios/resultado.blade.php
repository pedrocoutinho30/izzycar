@extends('layouts.admin')

@section('main-content')
<div class="container">
    <h2>Resultados</h2>

    <h4>Contagem por Região</h4>
    <ul>
        @foreach ($regioes as $regiao => $count)
            <li><strong>{{ $regiao }}:</strong> {{ $count }} anúncios</li>
        @endforeach
    </ul>

    <h4>Carro Mais Caro</h4>
    <p><strong>{{ $maisCaro['Título'] }}</strong> ({{ $maisCaro['Ano'] }}) - {{ $maisCaro['Preço'] }} € - {{ $maisCaro['Localidade'] }}</p>
    <a href="{{ $maisCaro['URL'] }}" target="_blank">Ver Anúncio</a>

    <h4>Carro Mais Barato</h4>
    <p><strong>{{ $maisBarato['Título'] }}</strong> ({{ $maisBarato['Ano'] }}) - {{ $maisBarato['Preço'] }} € - {{ $maisBarato['Localidade'] }}</p>
    <a href="{{ $maisBarato['URL'] }}" target="_blank">Ver Anúncio</a>

    <h4>Média de Preços</h4>
    <p>{{ number_format($mediaPreco, 2, ',', '.') }} €</p>

    <h4>Média de Tempo dos Anúncios</h4>
    <p>{{ number_format($mediaDias, 2, ',', '.') }} dias</p>

    <h4>Melhor Compra</h4>
    <p><strong>{{ $melhorCompra['Título'] }}</strong> ({{ $melhorCompra['Ano'] }}) - {{ $melhorCompra['Preço'] }} € - {{ $melhorCompra['Localidade'] }}</p>
    <a href="{{ $melhorCompra['URL'] }}" target="_blank">Ver Anúncio</a>
</div>
@endsection
