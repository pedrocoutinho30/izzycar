{{-- Campos extra do Modelo 9 IMT — requer $m9 (array, modelo9_dados atual) no scope --}}
@php
    $m9Text = [
        'categoria' => 'Categoria', 'tipo' => 'Tipo', 'cor' => 'Cor',
        'chassis' => 'Nº de quadro (chassis)', 'motor' => 'Nº de motor',
        'num_cilindros' => 'Nº de cilindros', 'cilindrada' => 'Cilindrada',
        'pneus_frente' => 'Pneumáticos — Frente', 'pneus_retaguarda' => 'Pneumáticos — Retaguarda',
        'peso_max_frente' => 'Peso máx. admissível — Frente', 'peso_max_retaguarda' => 'Peso máx. admissível — Retaguarda',
        'poder_elevacao' => 'Poder de elevação', 'tipo_caixa' => 'Tipo da caixa',
        'comprimento_caixa' => 'Comprimento máx. da caixa', 'largura_caixa' => 'Largura da caixa',
        'distancia_eixos' => 'Distância entre eixos', 'peso_bruto_total' => 'Peso bruto total',
        'tara' => 'Tara', 'portas_total' => 'Portas — Nº total', 'portas_direita' => 'Portas — Direita',
        'portas_esquerda' => 'Portas — Esquerda', 'portas_retaguarda' => 'Portas — Retaguarda',
        'lotacao' => 'Lotação', 'matricula_anterior' => 'Matrícula anterior',
        'pais_origem' => 'País de origem',
    ];
@endphp

<div class="row g-3">
    @foreach($m9Text as $key => $label)
    <div class="col-md-3">
        <label class="form-label small mb-1">{{ $label }}</label>
        <input type="text" name="modelo9[{{ $key }}]" class="form-control form-control-sm"
               value="{{ old('modelo9.' . $key, $m9[$key] ?? '') }}">
    </div>
    @endforeach

    <div class="col-md-3">
        <label class="form-label small mb-1">Data da matrícula anterior</label>
        <input type="date" name="modelo9[matricula_anterior_data]" class="form-control form-control-sm"
               value="{{ old('modelo9.matricula_anterior_data', $m9['matricula_anterior_data'] ?? '') }}">
    </div>

    <div class="col-12">
        <label class="form-label small mb-1">Anotações especiais</label>
        <textarea name="modelo9[anotacoes_especiais]" class="form-control form-control-sm" rows="2">{{ old('modelo9.anotacoes_especiais', $m9['anotacoes_especiais'] ?? '') }}</textarea>
    </div>

    <div class="col-12">
        <hr class="my-2">
        <label class="form-label small mb-2 fw-semibold">Pretensão Relativa a Veículos</label>
        <div class="row g-2">
            @foreach(\App\Services\Modelo9PdfService::PRETENSAO_LABELS as $key => $label)
            <div class="col-md-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="m9_{{ $key }}_{{ $idPrefix ?? '' }}"
                           name="modelo9[{{ $key }}]" value="1"
                           {{ old('modelo9.' . $key, $m9[$key] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label small" for="m9_{{ $key }}_{{ $idPrefix ?? '' }}">{{ $label }}</label>
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-2">
            <label class="form-label small mb-1">Motivo (se "Outra")</label>
            <input type="text" name="modelo9[pretensao_outra_motivo]" class="form-control form-control-sm"
                   value="{{ old('modelo9.pretensao_outra_motivo', $m9['pretensao_outra_motivo'] ?? '') }}">
        </div>
    </div>

    <div class="col-12 d-flex flex-wrap gap-4 mt-2">
        @foreach(['reboque' => 'Reboque?', 'rebocavel' => 'Rebocável', 'com_travao' => 'Com travão', 'sem_travao' => 'Sem travão'] as $key => $label)
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="m9_{{ $key }}_{{ $idPrefix ?? '' }}"
                   name="modelo9[{{ $key }}]" value="1"
                   {{ old('modelo9.' . $key, $m9[$key] ?? false) ? 'checked' : '' }}>
            <label class="form-check-label small" for="m9_{{ $key }}_{{ $idPrefix ?? '' }}">{{ $label }}</label>
        </div>
        @endforeach
    </div>
</div>
