@extends('layouts.admin')

@section('main-content')


<div class="max-w-4xl mx-auto p-6 m-6 bg-white rounded shadow">
    <div class="container-fluid px-4 py-5">
        <h1 class="text-2xl font-bold mb-2">{{ $car['title'] }}</h1>
        <p class="text-gray-600">{{ $car['year'] }} • {{ number_format($car['mileage'], 0, ',', '.') }} km • {{ $car['gearbox'] }}</p>
        <p class="text-xl font-semibold text-green-600 mt-2">Valor do carro: {{ number_format($car['price'], 2, ',', '.') }} € </p>
        <p class="text-gray-600 ">Preço médio similar: {{ number_format($avgPriceSimilar, 2, ',', '.') }} € </p>
        <p class="text-gray-600 ">Diferença de preço: <span class="{{ $priceDiff < 0 ? 'text-success' : 'text-danger' }}"> {{ number_format($priceDiff, 2, ',', '.') }} € ({{ $priceDiffPercent }}%)</span></p>

        <a href="{{ $car['url'] }}" target="_blank" class="inline-block mt-4 text-blue-500 underline">Ver no Standvirtual</a>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            {{-- Conforto --}}
            <div>
                <h2 class="text-lg font-semibold mb-2">Conforto</h2>
                <ul class="list-disc list-inside">
                    @if($car['banco_condutor_aquecido']) <li>Banco do condutor aquecido</li> @endif
                    @if($car['banco_passageiro_aquecido']) <li>Banco do passageiro aquecido</li> @endif
                    @if($car['fecho_central_sem_chave']) <li>Fecho central sem chave</li> @endif
                    @if($car['arranque_sem_chave']) <li>Arranque sem chave</li> @endif
                    @if($car['cruise_control']) <li>Cruise Control</li> @endif
                </ul>
            </div>

            {{-- Tecnologia --}}
            <div>
                <h2 class="text-lg font-semibold mb-2">Tecnologia</h2>
                <ul class="list-disc list-inside">
                    @if($car['apple_carplay']) <li>Apple CarPlay</li> @endif
                    @if($car['android_auto']) <li>Android Auto</li> @endif
                    @if($car['bluetooth']) <li>Bluetooth</li> @endif
                    @if($car['sistema_navegacao']) <li>Sistema de navegação</li> @endif
                </ul>
            </div>

            {{-- Assistência à condução --}}
            <div>
                <h2 class="text-lg font-semibold mb-2">Assistência à condução</h2>
                <ul class="list-disc list-inside">
                    @if($car['camara_marcha_atras']) <li>Câmara de marcha-atrás</li> @endif
                    @if($car['sensor_estacionamento_dianteiro']) <li>Sensores de estacionamento dianteiros</li> @endif
                    @if($car['sensor_estacionamento_traseiro']) <li>Sensores de estacionamento traseiros</li> @endif
                    @if($car['assistente_angulo_morto']) <li>Assistente de ângulo morto</li> @endif
                    @if($car['assistente_mudanca_faixa']) <li>Assistente de mudança de faixa</li> @endif
                    @if($car['controlo_proximidade']) <li>Controlo de proximidade</li> @endif
                </ul>
            </div>
        </div>
        <div class="mt-8">
            <a href="{{ url()->previous() }}" class="btn btn-danger">
                ← Voltar à listagem
            </a>
        </div>
    </div>
</div>
@endsection