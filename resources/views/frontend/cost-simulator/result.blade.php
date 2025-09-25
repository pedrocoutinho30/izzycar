@extends('frontend.partials.layout')


@section('content')

@include('frontend.partials.hero-section', ['title' => "Simulador de Custos", 'subtitle' => ""])

<section class="featured-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 text-center mt-4 mb-4">

            </div>
        </div>
    </div>
</section>


<section class="explore-section section-padding mt-4" class="bg-dark rounded shadow-sm">
    <div class="container mt-4">
        <div class="col-12 text-left mt-4 mb-4">
            <h2 class="text-accent ">Olá, {{ $name }}!</h2>
            <p class="text-black">Obrigado por utilizar o nosso simulador de custos. Com base nas informações que forneceu, aqui está uma estimativa detalhada dos custos associados à importação do seu veículo.</p>
        </div>

        <h5 class="text-accent ">Simulação</h5>


        <ul>
            <li class="text-black">
                <strong>Custo do carro</strong>: {{ number_format($valorCarro, 2) }} €
            </li>
            <li class="text-black">
                <strong>ISV</strong>: {{ number_format($isv, 2) }} €
            </li>
            <li class="text-black">
                <strong>Custos de serviço:</strong> {{ number_format($servicos, 2) }} €
            </li>

        </ul>
        <p class="text-black"><strong>Preço Chave na mão:</strong> {{ number_format($custoTotal, 2) }} €</p>
        <div class="col-md-12 mb-4 text-center">
            <a href="{{ route('frontend.cost-simulator') }}" class="btn btn-outline-form mt-3 text-black">Nova Simulação</a>
            <a href="{{ route('frontend.form-import') }}" class="btn btn-warning btn-lg mt-3">
                Quero importar
            </a>
        </div>
        <div class="mt-4 ">
            @if ($isv > 0)
            <h5 class="text-accent ">Tabela de Cálculo do ISV</h5>
            {!! $tableIsv !!}
            @endif
        </div>


</section>

@endsection