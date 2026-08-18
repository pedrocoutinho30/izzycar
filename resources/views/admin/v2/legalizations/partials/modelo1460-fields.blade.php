{{-- Campos extra do Mod. 1460/1 (Pedidos ISV) — requer $m1460 (array, modelo1460_dados atual) no scope --}}
<div class="row g-3">
    <div class="col-12">
        <label class="form-label small mb-2 fw-semibold">Identificação do Veículo</label>
        <p class="text-muted small mb-2">Marca, modelo, cilindrada e nº de quadro já vêm dos dados da legalização / Modelo 9.</p>
        <div class="d-flex flex-wrap gap-4 mb-2">
            @foreach(['novo' => 'Novo/sem matrícula', 'usado' => 'Usado'] as $key => $label)
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="m1460_{{ $key }}_{{ $idPrefix ?? '' }}"
                       name="modelo1460[{{ $key }}]" value="1"
                       {{ old('modelo1460.' . $key, $m1460[$key] ?? false) ? 'checked' : '' }}>
                <label class="form-check-label small" for="m1460_{{ $key }}_{{ $idPrefix ?? '' }}">{{ $label }}</label>
            </div>
            @endforeach
        </div>
    </div>

    <div class="col-md-3">
        <label class="form-label small mb-1">Emissão de CO2 (g/Km)</label>
        <input type="text" name="modelo1460[co2]" class="form-control form-control-sm"
               value="{{ old('modelo1460.co2', $m1460['co2'] ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label small mb-1">Partículas — 0, ___ (g/Km)</label>
        <input type="text" name="modelo1460[particulas]" class="form-control form-control-sm"
               value="{{ old('modelo1460.particulas', $m1460['particulas'] ?? '') }}">
    </div>

    <div class="col-12"><hr class="my-1"></div>

    <div class="col-md-6">
        <div class="form-check mb-1">
            <input class="form-check-input" type="checkbox" id="m1460_matricula_estrangeira_{{ $idPrefix ?? '' }}"
                   name="modelo1460[matricula_estrangeira]" value="1"
                   {{ old('modelo1460.matricula_estrangeira', $m1460['matricula_estrangeira'] ?? false) ? 'checked' : '' }}>
            <label class="form-check-label small fw-semibold" for="m1460_matricula_estrangeira_{{ $idPrefix ?? '' }}">Matrícula estrangeira</label>
        </div>
        <div class="row g-2">
            <div class="col-7">
                <label class="form-label small mb-1">País</label>
                <input type="text" name="modelo1460[pais_matricula_estrangeira]" class="form-control form-control-sm"
                       value="{{ old('modelo1460.pais_matricula_estrangeira', $m1460['pais_matricula_estrangeira'] ?? '') }}">
            </div>
            <div class="col-5">
                <label class="form-label small mb-1">Data da 1ª matrícula</label>
                <input type="date" name="modelo1460[data_primeira_matricula]" class="form-control form-control-sm"
                       value="{{ old('modelo1460.data_primeira_matricula', $m1460['data_primeira_matricula'] ?? '') }}">
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-check mb-1">
            <input class="form-check-input" type="checkbox" id="m1460_matricula_nacional_{{ $idPrefix ?? '' }}"
                   name="modelo1460[matricula_nacional]" value="1"
                   {{ old('modelo1460.matricula_nacional', $m1460['matricula_nacional'] ?? false) ? 'checked' : '' }}>
            <label class="form-check-label small fw-semibold" for="m1460_matricula_nacional_{{ $idPrefix ?? '' }}">Matrícula nacional</label>
        </div>
        <div class="row g-2">
            <div class="col-6">
                <label class="form-label small mb-1">Data da matrícula</label>
                <input type="date" name="modelo1460[data_matricula_nacional]" class="form-control form-control-sm"
                       value="{{ old('modelo1460.data_matricula_nacional', $m1460['data_matricula_nacional'] ?? '') }}">
            </div>
        </div>
    </div>

    <div class="col-12"><hr class="my-1"></div>

    <div class="col-md-4">
        <label class="form-label small mb-1">DAV n.º</label>
        <input type="text" name="modelo1460[dav_numero]" class="form-control form-control-sm"
               value="{{ old('modelo1460.dav_numero', $m1460['dav_numero'] ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label small mb-1">Data DAV</label>
        <input type="date" name="modelo1460[dav_data]" class="form-control form-control-sm"
               value="{{ old('modelo1460.dav_data', $m1460['dav_data'] ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label small mb-1">Alfândega</label>
        <input type="text" name="modelo1460[dav_alfandega]" class="form-control form-control-sm"
               value="{{ old('modelo1460.dav_alfandega', $m1460['dav_alfandega'] ?? '') }}">
    </div>

    <div class="col-12">
        <hr class="my-2">
        <label class="form-label small mb-2 fw-semibold">Pedidos</label>
        <div class="form-check mb-1">
            <input class="form-check-input" type="checkbox" id="m1460_pedido_1_{{ $idPrefix ?? '' }}"
                   name="modelo1460[pedido_1_beneficio_fiscal]" value="1"
                   {{ old('modelo1460.pedido_1_beneficio_fiscal', $m1460['pedido_1_beneficio_fiscal'] ?? false) ? 'checked' : '' }}>
            <label class="form-check-label small" for="m1460_pedido_1_{{ $idPrefix ?? '' }}">1. Benefício fiscal</label>
        </div>
        <div class="d-flex align-items-center gap-2 ms-4">
            <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" id="m1460_pedido_11_{{ $idPrefix ?? '' }}"
                       name="modelo1460[pedido_1_1_isencao_isv]" value="1"
                       {{ old('modelo1460.pedido_1_1_isencao_isv', $m1460['pedido_1_1_isencao_isv'] ?? false) ? 'checked' : '' }}>
                <label class="form-check-label small" for="m1460_pedido_11_{{ $idPrefix ?? '' }}">1.1 Isenção do ISV, ao abrigo do regime previsto no art.º</label>
            </div>
            <input type="text" name="modelo1460[pedido_1_1_artigo]" class="form-control form-control-sm" style="width: 80px;"
                   value="{{ old('modelo1460.pedido_1_1_artigo', $m1460['pedido_1_1_artigo'] ?? '') }}" placeholder="nº art.º">
            <span class="small text-muted">do CISV</span>
        </div>
    </div>

    <div class="col-12">
        <hr class="my-2">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="m1460_preencher_representante_{{ $idPrefix ?? '' }}"
                   name="modelo1460[preencher_representante]" value="1"
                   {{ old('modelo1460.preencher_representante', $m1460['preencher_representante'] ?? true) ? 'checked' : '' }}>
            <label class="form-check-label small fw-semibold" for="m1460_preencher_representante_{{ $idPrefix ?? '' }}">Preencher dados do representante</label>
        </div>
        <p class="text-muted small mb-0 ms-4">Se desmarcado, a secção "Representante Legal" (página 3) fica em branco.</p>
    </div>
</div>
