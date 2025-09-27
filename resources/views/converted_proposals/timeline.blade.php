@extends('frontend.partials.layout')

@section('title', 'Izzycar - Linha do Tempo da Proposta')

@section('content')
<section class="section-padding bg-dark rounded shadow-sm">
    <div class="container py-5">


        <div class="row">
            <div class="col-md-4 col-4 text-start">
                <div class="mt-3">
                    <h5 class="text-white">{{ $convertedProposal->brand }} {{ $convertedProposal->model }} {{ $convertedProposal->version }}</h5>
                    <img src="{{ $convertedProposal->proposal->images }}" alt="Logo Izzycar" style="height:auto; width:420px;">

                    <p class="text-muted mb-1">{{ $convertedProposal->year }} | {{ $convertedProposal->km }} km</p>
                    <p class="text-muted mb-1">Cliente: {{ $convertedProposal->proposal->client->name }}</p>
                    <p class="text-muted mb-1">Data da Proposta: {{ \Carbon\Carbon::parse($convertedProposal->proposal->created_at)->isoFormat('DD-MM-YYYY') }}</p>
                </div>
            </div>
            <div class="col-md-8 col-8 text-center">
                @php
                // Extrair apenas os status já concluídos
                $completedStatuses = collect($status_history)->pluck('new_status')->toArray();

                $lastCompletedIndex = null;
                foreach ($allStatus as $i => $status) {
                if (in_array($status['status'], $completedStatuses)) {
                $lastCompletedIndex = $i;
                }
                }
                $totalSteps = count($allStatus);
                $progressPercent = $lastCompletedIndex !== null
                ? (($lastCompletedIndex + 1) / $totalSteps) * 100
                : 0;


                // Garantir que o progresso não ultrapassa 100%
                $progressPercent = min($progressPercent, 100);


                @endphp

                <h2 class="mb-4 text-center text-accent">Passos do processo</h2>


                <!-- Verificar se algum dos estados é cancelado. Caso seja não mostrar nada para baixo -->
                @if(in_array('Cancelado', $completedStatuses))
                <p class="text-danger">Processo Cancelado</p>
                <p class="text-white">O processo foi cancelado. Por favor, contacte a Izzycar para mais informações.</p>
                @else
                <div class="col-lg-10 col-12 mx-auto">
                    <div class="timeline-container">
                        <div class="list-progress">
                            <div class="inner" style="height: {{ $progressPercent }}%;"></div>
                        </div>
                        <ul class="vertical-scrollable-timeline">
                            <ul class="vertical-scrollable-timeline">
                                @foreach ($allStatus as $i => $status)
                                @php
                                $isActive = in_array($status['status'], $completedStatuses);
                                $isLastActive = $isActive && $i === $lastCompletedIndex;
                                @endphp

                                <li class="timeline-item {{ $isActive ? 'active' : '' }}"
                                    @if($isLastActive) id="last-active" @endif>

                                    <div class="icon-holder">
                                        <i class="{{ $status['icon'] }}"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h4 class="text-accent mb-3">{{ $status['status'] }}</h4>
                                        @php
                                        $statusEntry = collect($status_history)->firstWhere('new_status', $status['status']);
                                        @endphp
                                        @if ($statusEntry)
                                        <p class="text-white mb-1">
                                            {{ \Carbon\Carbon::parse($statusEntry->created_at)->locale('pt_PT')->isoFormat('DD-MM-YYYY HH:mm') }}
                                        </p>
                                        <p>{{ $statusEntry->notes }}</p>
                                        @else
                                        <p class="text-white">Por concluir</p>
                                        @endif
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                    </div>
                </div>
                @endif
            </div>

        </div>
    </div>
</section>
@endsection

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const lastActive = document.getElementById("last-active");
        if (lastActive) {
            lastActive.scrollIntoView({
                behavior: "smooth", // anima o scroll
                block: "center" // centraliza o item no ecrã
            });
        }
    });
</script>