@extends('layouts.admin-v2')

@section('title', 'Análise de Carros')

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">
                <i class="bi bi-bar-chart-line"></i> Análise de Carros Usados
            </h1>
            <p class="text-muted mb-0">Importa um ficheiro JSON do scraper e analisa os preços do mercado</p>
        </div>
    </div>

    {{-- Upload card --}}
    <div class="modern-card mb-4">
        <div class="modern-card-header">
            <h5 class="modern-card-title">
                <i class="bi bi-cloud-upload"></i> Importar ficheiro
            </h5>
        </div>
        <div class="modern-card-body">

            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                @foreach ($errors->all() as $error)
                    <div><i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $error }}</div>
                @endforeach
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <form action="{{ route('car-analysis.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row align-items-end g-3">
                    <div class="col-md-7 col-lg-5">
                        <label for="json_file" class="form-label fw-semibold">Ficheiro JSON (scraper)</label>
                        <input type="file" name="json_file" id="json_file"
                               accept=".json,application/json" class="form-control">
                        <div class="form-text">Formato: <code>Marca_Modelo.json</code> gerado pelo scraper</div>
                    </div>
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-bar-chart-line me-1"></i> Analisar
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>

    @isset($metricas)

    {{-- Stats cards --}}
    @include('components.admin.stats-cards', [
        'stats' => [
            [
                'title'  => 'Carros analisados',
                'value'  => $metricas['total'],
                'icon'   => 'bi-car-front',
                'color'  => 'primary',
            ],
            [
                'title'  => 'Média de preço',
                'value'  => number_format($metricas['media_preco'], 0, ',', ' ') . ' €',
                'icon'   => 'bi-graph-up',
                'color'  => 'info',
            ],
            [
                'title'  => 'Mediana',
                'value'  => number_format($metricas['mediano_preco'], 0, ',', ' ') . ' €',
                'icon'   => 'bi-align-center',
                'color'  => 'secondary',
            ],
            [
                'title'  => 'Mínimo',
                'value'  => number_format($metricas['min_preco'], 0, ',', ' ') . ' €',
                'icon'   => 'bi-arrow-down-circle',
                'color'  => 'success',
            ],
            [
                'title'  => 'Máximo',
                'value'  => number_format($metricas['max_preco'], 0, ',', ' ') . ' €',
                'icon'   => 'bi-arrow-up-circle',
                'color'  => 'danger',
            ],
            [
                'title'  => 'Média km',
                'value'  => number_format($metricas['media_kms'], 0, ',', ' ') . ' km',
                'icon'   => 'bi-speedometer2',
                'color'  => 'warning',
            ],
        ]
    ])

    {{-- Tabela de resultados --}}
    <div class="modern-card">
        <div class="modern-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h5 class="modern-card-title mb-0">
                <i class="bi bi-table"></i> Resultados
                <span class="badge bg-secondary ms-2">{{ $carros->count() }}</span>
            </h5>
            <div style="min-width: 220px;">
                <input type="text" id="tableSearch" class="form-control form-control-sm"
                       placeholder="&#xF52A; Pesquisar...">
            </div>
        </div>
        <div class="modern-card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0" id="analysisTable">
                    <thead class="table-light">
                        <tr>
                            <th>Marca / Modelo</th>
                            <th>Submodelo</th>
                            <th class="text-center">Ano</th>
                            <th class="text-end">Kms</th>
                            <th class="text-end">Preço</th>
                            <th class="text-end" title="Preço Justo de Mercado">PJM</th>
                            <th class="text-center"># comp.</th>
                            <th class="text-center">Score</th>
                            <th class="text-center" title="Keyless-go"><i class="bi bi-key"></i></th>
                            <th class="text-center" title="Jantes de liga leve"><i class="bi bi-circle"></i></th>
                            <th class="text-center" title="Reconhecimento dos sinais de trânsito"><i class="bi bi-sign-stop"></i></th>
                            <th class="text-center" title="Apple CarPlay"><i class="bi bi-apple"></i></th>
                            <th class="text-center" title="Android Auto"><i class="bi bi-android2"></i></th>
                            <th class="text-center" title="Sensor de estacionamento dianteiro"><span style="font-size:.7rem">Sen.D</span></th>
                            <th class="text-center" title="Sensor de estacionamento traseiro"><span style="font-size:.7rem">Sen.T</span></th>
                            <th class="text-center" title="Câmara de marcha atrás"><i class="bi bi-camera-video"></i></th>
                            <th class="text-center" title="Teto de abrir"><i class="bi bi-sun"></i></th>
                            <th class="text-center" title="Assistente de ângulo morto"><i class="bi bi-eye"></i></th>
                            <th class="text-center">Classificação</th>
                            @if($carros->first() && isset($carros->first()['localizacao']))
                            <th><i class="bi bi-geo-alt"></i></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($carros as $carro)
                        @php
                            $score  = $carro['score'];
                            $classif = $carro['classificacao'];

                            if ($score < -0.10) {
                                $badgeColor = 'success';
                                $rowClass   = 'table-success';
                                $icon       = 'bi-emoji-laughing-fill';
                            } elseif ($score < -0.05) {
                                $badgeColor = 'success';
                                $rowClass   = '';
                                $icon       = 'bi-hand-thumbs-up-fill';
                            } elseif ($score <= 0.05) {
                                $badgeColor = 'warning';
                                $rowClass   = '';
                                $icon       = 'bi-dash-circle-fill';
                            } elseif ($score <= 0.10) {
                                $badgeColor = 'danger';
                                $rowClass   = '';
                                $icon       = 'bi-hand-thumbs-down-fill';
                            } else {
                                $badgeColor = 'danger';
                                $rowClass   = 'table-danger';
                                $icon       = 'bi-emoji-frown-fill';
                            }

                            $signedScore = ($score >= 0 ? '+' : '') . number_format($score * 100, 1) . '%';
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td class="fw-semibold">
                                @if(!empty($carro['link'] ?? ''))
                                    <a href="{{ $carro['link'] }}" target="_blank" rel="noopener">
                                        {{ $carro['marca_modelo'] }}
                                        <i class="bi bi-box-arrow-up-right ms-1 text-muted" style="font-size:.75rem;"></i>
                                    </a>
                                @else
                                    {{ $carro['marca_modelo'] }}
                                @endif
                            </td>
                            <td class="text-muted small">{{ $carro['submodelo'] ?? '—' }}</td>
                            <td class="text-center">{{ $carro['ano'] }}</td>
                            <td class="text-end text-nowrap">{{ number_format($carro['kms'], 0, ',', ' ') }} km</td>
                            <td class="text-end text-nowrap fw-semibold">{{ number_format($carro['preco'], 0, ',', ' ') }} €</td>
                            <td class="text-end text-nowrap text-muted">{{ number_format($carro['pjm'], 0, ',', ' ') }} €</td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border" title="Carros comparáveis usados no cálculo">
                                    {{ $carro['num_comparaveis'] }}
                                </span>
                            </td>
                            <td class="text-center text-nowrap">
                                <span class="badge bg-{{ $badgeColor }} bg-opacity-75">
                                    {{ $signedScore }}
                                </span>
                            </td>
                            @php
                                $feats = $carro['features'] ?? [];
                                $featFlags = [
                                    'Keyless-go'                            => !empty($feats['Keyless-go']),
                                    'Jantes de liga leve'                   => !empty($feats['Jantes de liga leve']),
                                    'Reconhecimento dos sinais de trânsito' => !empty($feats['Reconhecimento dos sinais de trânsito']),
                                    'Apple CarPlay'                         => !empty($feats['Apple CarPlay']),
                                    'Android Auto'                          => !empty($feats['Android Auto']),
                                    'Sensor de estacionamento dianteiro'    => !empty($feats['Sensor de estacionamento dianteiro']),
                                    'Sensor de estacionamento traseiro'     => !empty($feats['Sensor de estacionamento traseiro']),
                                    'Câmara de marcha atrás'               => !empty($feats['Câmara de marcha atrás']),
                                    'Teto de abrir'                         => !empty($feats['Teto de abrir']),
                                    'Assistente de ângulo morto'            => !empty($feats['Assistente de ângulo morto']),
                                ];
                            @endphp
                            @foreach($featFlags as $feat => $tem)
                            <td class="text-center">
                                @if($tem)
                                    <i class="bi bi-check-circle-fill text-success" title="{{ $feat }}"></i>
                                @else
                                    <i class="bi bi-x-circle text-muted opacity-25" title="{{ $feat }}"></i>
                                @endif
                            </td>
                            @endforeach
                            <td class="text-center text-nowrap">
                                <span class="badge bg-{{ $badgeColor }} bg-opacity-10 text-{{ $badgeColor }} border border-{{ $badgeColor }} border-opacity-25">
                                    <i class="bi {{ $icon }} me-1"></i>{{ $classif }}
                                </span>
                            </td>
                            @if(!empty($carro['localizacao'] ?? null))
                            <td class="text-muted small text-nowrap">
                                <i class="bi bi-geo-alt me-1"></i>{{ $carro['localizacao'] }}
                            </td>
                            @elseif($carros->first() && isset($carros->first()['localizacao']))
                            <td>—</td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @endisset

</div>
@endsection

@push('styles')
<style>
    .modern-card {
        background: #fff;
        border-radius: .75rem;
        border: 1px solid #e9ecef;
        box-shadow: 0 1px 4px rgba(0,0,0,.06);
        margin-bottom: 1.25rem;
    }
    .modern-card-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f0f2f5;
        display: flex;
        align-items: center;
    }
    .modern-card-title {
        margin: 0;
        font-size: .95rem;
        font-weight: 600;
        color: #344767;
    }
    .modern-card-body {
        padding: 1.25rem;
    }

    /* Pesquisa highlight */
    mark.search-highlight {
        background: #fff3cd;
        padding: 0;
        border-radius: 2px;
    }

    /* Tabela */
    #analysisTable tbody tr:hover td {
        background-color: rgba(0,0,0,.02);
    }

    /* Cabeçalho fixo */
    #analysisTable thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f8f9fa;
        box-shadow: inset 0 -1px 0 #dee2e6;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('tableSearch');
    if (!input) return;

    input.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#analysisTable tbody tr');

        rows.forEach(function (row) {
            const text = row.textContent.toLowerCase();
            row.style.display = (!q || text.includes(q)) ? '' : 'none';
        });
    });
});
</script>
@endpush
