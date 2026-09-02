@extends('layouts.admin-v2')

@section('title', 'Criador de Posts — Recomendação')

@section('content')

@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
['icon' => 'bi bi-magic', 'label' => 'Criador de Posts', 'href' => route('admin.v2.social-posts.index')],
['icon' => '', 'label' => isset($post) ? 'Editar' : 'Novo'],
],
'title' => 'Criador de Posts — Recomendação',
'subtitle' => 'Preenche os dados do carro, guarda o post e descarrega o carrossel de 2 imagens, já no design da IzzyCar.',
'actionHref' => route('admin.v2.social-posts.index'),
'actionLabel' => 'Ver Posts Guardados'
])

<div class="row g-4">
    <!-- FORMULÁRIO -->
    <div class="col-lg-5">
        <form method="POST" action="{{ route('admin.v2.social-posts.store') }}" enctype="multipart/form-data">
            @csrf
            @if(isset($post))
            <input type="hidden" name="id" value="{{ $post->id }}">
            @endif
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-car-front"></i>
                        Dados do Carro
                    </h5>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Marca</label>
                        <input type="text" name="brand" id="f_brand" class="form-control" placeholder="Mercedes" value="{{ old('brand', $post->brand ?? '') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Modelo</label>
                        <input type="text" name="model" id="f_model" class="form-control" placeholder="EQS 450+ AMG" value="{{ old('model', $post->model ?? '') }}" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Versão / detalhe (opcional)</label>
                        <input type="text" name="version" id="f_version" class="form-control" placeholder="Line Premium Plus" value="{{ old('version', $post->version ?? '') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Quilómetros</label>
                        <input type="number" name="mileage" id="f_mileage" class="form-control" min="0" placeholder="80000" value="{{ old('mileage', $post->mileage ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Potência (cv)</label>
                        <input type="number" name="power" id="f_power" class="form-control" min="0" placeholder="328" value="{{ old('power', $post->power ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Combustível</label>
                        <select name="fuel" id="f_fuel" class="form-select">
                            <option value="">Selecione</option>
                            @foreach(['Gasolina', 'Diesel', 'Elétrico', 'Híbrido Plug-in/Gasolina', 'Híbrido Plug-in/Diesel'] as $fuelOption)
                            <option value="{{ $fuelOption }}" {{ old('fuel', $post->fuel ?? 'Elétrico') === $fuelOption ? 'selected' : '' }}>{{ $fuelOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Ano</label>
                        <input type="number" name="year" id="f_year" class="form-control" min="1990" max="{{ date('Y') + 1 }}" placeholder="{{ date('Y') }}" value="{{ old('year', $post->year ?? '') }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Equipamento em destaque <span class="text-muted">(um por linha, máx. 4)</span></label>
                        <textarea name="equipment_raw" id="f_equipment" class="form-control" rows="4" placeholder="Teto panorâmico&#10;Sistema de som Burmester&#10;GUARD 360">{{ old('equipment_raw', isset($post) ? implode("\n", $post->equipment ?? []) : '') }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Preço chave na mão (€)</label>
                        <input type="number" name="price" id="f_price" class="form-control" min="0" placeholder="58200" value="{{ old('price', $post->price ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Poupança estimada (€)</label>
                        <input type="number" name="savings" id="f_savings" class="form-control" min="0" placeholder="11800" value="{{ old('savings', $post->savings ?? '') }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">URL do anúncio</label>
                        <input type="url" name="url" id="f_url" class="form-control" placeholder="https://..." value="{{ old('url', $post->url ?? '') }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Imagem do carro</label>
                        <input type="file" name="image" id="f_image" class="form-control" accept="image/*">
                        <div class="form-text">A imagem é dividida automaticamente ao meio entre os 2 slides.</div>
                        @if(isset($post) && $post->image)
                        <div class="form-text">Já tens uma foto guardada — só precisas de escolher uma nova se quiseres substituí-la.</div>
                        @endif
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> {{ isset($post) ? 'Atualizar Post' : 'Guardar Post' }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- PRÉ-VISUALIZAÇÃO -->
    <div class="col-lg-7">
        <div class="modern-card mb-4" style="display:none;">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-eye"></i>
                    Design 1 — Cartão
                </h5>
            </div>

            <div class="d-flex flex-wrap gap-4 justify-content-center">
                <div class="text-center">
                    <div class="post-preview-frame" id="previewFrame1"></div>
                    <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="downloadBtn1">
                        <i class="bi bi-download"></i> Descarregar Slide 1
                    </button>
                </div>
                <div class="text-center">
                    <div class="post-preview-frame" id="previewFrame2"></div>
                    <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="downloadBtn2">
                        <i class="bi bi-download"></i> Descarregar Slide 2
                    </button>
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="button" class="btn btn-primary" id="downloadBothBtn">
                    <i class="bi bi-download"></i> Descarregar os 2 slides
                </button>
            </div>
        </div>

        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-eye"></i>
                    Design 2 — Editorial
                </h5>
            </div>

            <div class="d-flex flex-wrap gap-4 justify-content-center">
                <div class="text-center">
                    <div class="post-preview-frame" id="previewFrame1V2"></div>
                    <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="downloadBtn1V2">
                        <i class="bi bi-download"></i> Descarregar Slide 1
                    </button>
                </div>
                <div class="text-center">
                    <div class="post-preview-frame" id="previewFrame2V2"></div>
                    <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="downloadBtn2V2">
                        <i class="bi bi-download"></i> Descarregar Slide 2
                    </button>
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="button" class="btn btn-primary" id="downloadBothBtnV2">
                    <i class="bi bi-download"></i> Descarregar os 2 slides
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Alvos de exportação, fora do ecrã, sempre ao tamanho real (1080x1080) -->
<div style="position:absolute; left:-99999px; top:0;">
    <div id="exportFrame1"></div>
    <div id="exportFrame2"></div>
    <div id="exportFrame1V2"></div>
    <div id="exportFrame2V2"></div>
</div>

<style>
    .post-preview-frame {
        width: 340px;
        height: 340px;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.25);
        background: #0b0906;
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
(function () {
    const LOGO_URL = '{{ asset('img/logo-transparente.png') }}';
    const GOLD = '#d9b25c';

    const state = {
        imageDataUrl: @json(isset($post) && $post->image ? Illuminate\Support\Facades\Storage::url($post->image) : null),
    };

    function getFormData() {
        const equipmentLines = document.getElementById('f_equipment').value
            .split('\n')
            .map(l => l.trim())
            .filter(l => l.length > 0)
            .slice(0, 4);

        return {
            brand: document.getElementById('f_brand').value.trim(),
            model: document.getElementById('f_model').value.trim(),
            version: document.getElementById('f_version').value.trim(),
            mileage: document.getElementById('f_mileage').value,
            power: document.getElementById('f_power').value,
            fuel: document.getElementById('f_fuel').value,
            year: document.getElementById('f_year').value,
            equipment: equipmentLines,
            price: document.getElementById('f_price').value,
            savings: document.getElementById('f_savings').value,
            url: document.getElementById('f_url').value.trim(),
            image: state.imageDataUrl,
        };
    }

    function fmtNumber(v) {
        if (v === '' || v === null || v === undefined) return null;
        return Number(v).toLocaleString('pt-PT');
    }

    function fmtEuro(v) {
        const n = fmtNumber(v);
        return n === null ? null : n + ' €';
    }

    /**
     * Gera o HTML de um slide, com todas as medidas proporcionais a "size"
     * (a base de desenho é sempre 1080px). Usado tanto para a pré-visualização
     * pequena como para o alvo de exportação a tamanho real.
     */
    function px(size, base1080) {
        return (size / 1080 * base1080).toFixed(2) + 'px';
    }

    // Largura de referência do card de texto (a foto continua a ocupar o slide todo por trás).
    const CARD_RATIO = 0.56;

    function fullBackgroundStyle(data, side) {
        if (!data.image) {
            return `background: radial-gradient(120% 140% at 50% 0%, #241c14 0%, #14100c 60%, #0b0906 100%);`;
        }
        const position = side === 'left' ? 'left center' : 'right center';
        return `background-image: linear-gradient(180deg, rgba(5,4,3,0.35) 0%, rgba(5,4,3,0.05) 25%, rgba(5,4,3,0.1) 55%, rgba(5,4,3,0.5) 100%), url('${data.image}');
                background-size: 100% 100%, 200% 100%;
                background-position: center, ${position};
                background-repeat: no-repeat;`;
    }

    /**
     * Card arredondado que destaca o texto sem esconder totalmente o carro por trás.
     * Nota: não usa backdrop-filter (desfoque) porque o html2canvas, usado para
     * exportar a imagem final, não o suporta — ficaria diferente no download da
     * pré-visualização. Em vez disso, usa uma opacidade alta e uniforme (sem
     * desfoque, mas também sem arestas visíveis do carro por trás), que fica
     * igual em ambos os casos.
     */
    function textCardStyle(size, align) {
        const radius = px(size, 28);
        return `border-radius:${radius}; background:linear-gradient(160deg, rgba(9,7,6,0.96) 0%, rgba(9,7,6,0.9) 100%); border:1px solid rgba(255,255,255,0.08); box-shadow:0 ${px(size,20)} ${px(size,50)} rgba(0,0,0,0.35);`;
    }

    function headerOverlayHtml(size, pageLabel) {
        const pad = px(size, 44);
        return `
            <div style="position:absolute; inset:0; pointer-events:none; background: radial-gradient(circle at top left, rgba(0,0,0,0.5), transparent 45%), radial-gradient(circle at top right, rgba(0,0,0,0.5), transparent 45%);"></div>
            <div style="position:absolute; top:${pad}; left:${pad}; right:${pad}; display:flex; align-items:flex-start; justify-content:space-between;">
                <div style="width:${px(size,150)}; height:${px(size,100)};">
                    <img src="${LOGO_URL}" style="width:100%; height:100%; object-fit:contain; object-position:left top;">
                </div>
                <span style="font-size:${px(size,13)}; font-weight:700; color:#fff; background:rgba(255,255,255,0.14); border-radius:999px; padding:${px(size,4)} ${px(size,12)};">${pageLabel}</span>
            </div>`;
    }

    // Largura do card de texto, na base de 1080px (mesma margem dos dois lados).
    const CARD_MARGIN = 44;
    const CARD_WIDTH_BASE = 1080 * CARD_RATIO - CARD_MARGIN;

    function slide1Html(size, data) {
        const title = [data.brand, data.model].filter(Boolean).join(' ') || 'Marca Modelo';

        const features = [
            { icon: '🛣️', value: data.mileage ? fmtNumber(data.mileage) + ' km' : null, label: 'Quilómetros' },
            { icon: '⚡', value: data.power ? data.power + ' cv' : null, label: 'Potência' },
            { icon: '⛽', value: data.fuel || null, label: 'Combustível' },
            { icon: '📅', value: data.year || null, label: 'Ano' },
        ].filter(f => f.value);

        const featureRows = features.map(f => `
            <div style="display:flex; align-items:center; gap:${px(size,14)}; margin-bottom:${px(size,16)};">
                <div style="width:${px(size,38)}; height:${px(size,38)}; border-radius:${px(size,10)}; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.14); display:flex; align-items:center; justify-content:center; font-size:${px(size,17)}; flex-shrink:0;">${f.icon}</div>
                <div>
                    <div style="font-size:${px(size,21)}; font-weight:800; color:#fff; line-height:1.15;">${f.value}</div>
                    <div style="font-size:${px(size,13)}; color:#c7c7c7;">${f.label}</div>
                </div>
            </div>`).join('');

        const equipmentHtml = data.equipment.length ? `
            <div style="margin-top:${px(size,18)};">
                <div style="font-size:${px(size,15)}; font-weight:800; color:#fff; margin-bottom:${px(size,9)}; display:flex; align-items:center; gap:${px(size,7)};">
                    <span>⚙️</span> Equipamento
                </div>
                ${data.equipment.map(e => `
                    <div style="font-size:${px(size,14)}; color:#e9e9e9; margin-bottom:${px(size,5)}; padding-left:${px(size,3)};">• ${e}</div>
                `).join('')}
            </div>` : '';

        return `
            <div style="width:${size}px; height:${size}px; position:relative; font-family:'Inter',-apple-system,'Helvetica Neue',Arial,sans-serif; color:#fff; ${fullBackgroundStyle(data, 'left')} overflow:hidden;">
                <div style="position:absolute; top:${px(size,150)}; left:${px(size,CARD_MARGIN)}; width:${px(size,CARD_WIDTH_BASE)}; padding:${px(size,30)}; box-sizing:border-box; ${textCardStyle(size)}">
                    <div style="font-size:${px(size,14)}; font-weight:800; letter-spacing:${px(size,2.5)}; text-transform:uppercase; color:${GOLD}; margin-bottom:${px(size,9)};">RECOMENDAÇÃO</div>
                    <div style="font-size:${px(size,30)}; font-weight:800; line-height:1.12; margin-bottom:${px(size,4)};">${title}</div>
                    ${data.version ? `<div style="font-size:${px(size,15)}; color:#cfcfcf; margin-bottom:${px(size,8)};">${data.version}</div>` : ''}
                    <div style="margin-top:${px(size,20)};">
                        ${featureRows}
                        ${equipmentHtml}
                    </div>
                </div>
                ${headerOverlayHtml(size, '1/2')}
            </div>`;
    }

    function slide2Html(size, data) {
        const price = fmtEuro(data.price);
        const savings = fmtEuro(data.savings);

        return `
            <div style="width:${size}px; height:${size}px; position:relative; font-family:'Inter',-apple-system,'Helvetica Neue',Arial,sans-serif; color:#fff; ${fullBackgroundStyle(data, 'right')} overflow:hidden;">
                <div style="position:absolute; top:${px(size,150)}; right:${px(size,CARD_MARGIN)}; width:${px(size,CARD_WIDTH_BASE)}; padding:${px(size,30)}; box-sizing:border-box; ${textCardStyle(size)}">
                    <div style="font-size:${px(size,14)}; font-weight:800; letter-spacing:${px(size,2.5)}; text-transform:uppercase; color:${GOLD}; margin-bottom:${px(size,16)};">PROPOSTA IZZYCAR</div>
                    ${price ? `
                    <div style="font-size:${px(size,16)}; font-weight:700; color:#d9d9d9;">Preço chave na mão</div>
                    <div style="font-size:${px(size,36)}; font-weight:800; color:#fff; margin-bottom:${px(size,18)}; line-height:1.1;">${price}</div>
                    <div style="height:1px; background:rgba(255,255,255,0.2); margin-bottom:${px(size,18)};"></div>` : ''}
                    ${savings ? `
                    <div style="font-size:${px(size,16)}; font-weight:700; color:#d9d9d9;">Poupança estimada</div>
                    <div style="font-size:${px(size,36)}; font-weight:800; color:#4ade80; line-height:1.1;">${savings}</div>` : ''}
                </div>
                <div style="position:absolute; bottom:${px(size,44)}; right:${px(size,CARD_MARGIN)}; width:${px(size,CARD_WIDTH_BASE)}; box-sizing:border-box; background:linear-gradient(135deg, #6e0707 0%, #990000 100%); border-radius:${px(size,16)}; padding:${px(size,16)} ${px(size,18)}; box-shadow:0 ${px(size,14)} ${px(size,34)} rgba(110,7,7,0.4);">
                    <div style="font-size:${px(size,14.5)}; font-weight:800; margin-bottom:${px(size,4)};">Simular a minha importação</div>
                    <div style="font-size:${px(size,13)}; font-weight:700; color:#ffd8d8;">izzycar.pt →</div>
                </div>
                ${headerOverlayHtml(size, '2/2')}
            </div>`;
    }

    /**
     * Design 2 (Editorial): sem cartão opaco atrás do texto — o texto assenta
     * diretamente sobre a foto, com um gradiente escuro apenas do lado do
     * texto para garantir legibilidade. Ícones em círculo contornado e
     * linhas douradas a separar cada bloco, à semelhança do moodboard
     * fornecido pelo cliente.
     */
    function fullBackgroundStyleV2(data, textSide) {
        if (!data.image) {
            const pos = textSide === 'left' ? '20% 10%' : '80% 10%';
            return `background: radial-gradient(130% 140% at ${pos}, #241c14 0%, #14100c 55%, #0b0906 100%);`;
        }
        // Mantém o mesmo truque de "metade da imagem por slide" do Design 1.
        const imgAnchor = textSide === 'left' ? 'left center' : 'right center';
        const darkAngle = textSide === 'left' ? '90deg' : '270deg';
        const radialPos = textSide === 'left' ? '10% 0%' : '90% 0%';
        return `background-image: linear-gradient(${darkAngle}, rgba(5,4,3,0.95) 0%, rgba(5,4,3,0.74) 32%, rgba(5,4,3,0.3) 58%, rgba(5,4,3,0.05) 82%), radial-gradient(140% 120% at ${radialPos}, rgba(0,0,0,0.55), transparent 50%), url('${data.image}');
                background-size: 100% 100%, 100% 100%, 200% 100%;
                background-position: center, center, ${imgAnchor};
                background-repeat: no-repeat;`;
    }

    function goldDividerV2(size, width) {
        return `<div style="height:1px; width:${px(size, width)}; background:linear-gradient(90deg, ${GOLD}, rgba(217,178,92,0));"></div>`;
    }

    function slide1HtmlV2(size, data) {
        const title = [data.brand, data.model].filter(Boolean).join(' ') || 'Marca Modelo';

        const features = [
            { icon: 'bi-signpost-split', value: data.mileage ? fmtNumber(data.mileage) + ' km' : null, label: 'Quilómetros' },
            { icon: 'bi-speedometer2', value: data.power ? data.power + ' cv' : null, label: 'Potência' },
            { icon: 'bi-fuel-pump', value: data.fuel || null, label: 'Combustível' },
            { icon: 'bi-calendar3', value: data.year || null, label: 'Ano' },
        ].filter(f => f.value);

        const featureRows = features.map(f => `
            <div style="margin-bottom:${px(size,16)};">
                <div style="display:flex; align-items:center; gap:${px(size,16)}; margin-bottom:${px(size,12)};">
                    <div style="width:${px(size,46)}; height:${px(size,46)}; border-radius:50%; border:${px(size,2)} solid rgba(255,255,255,0.55); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="bi ${f.icon}" style="font-size:${px(size,20)}; color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:${px(size,26)}; font-weight:800; color:#fff; line-height:1.1;">${f.value}</div>
                        <div style="font-size:${px(size,16)}; color:#d8d8d8;">${f.label}</div>
                    </div>
                </div>
                ${goldDividerV2(size, 470)}
            </div>`).join('');

        const equipmentHtml = data.equipment.length ? `
            <div style="margin-top:${px(size,8)};">
                <div style="display:flex; align-items:center; gap:${px(size,10)}; font-size:${px(size,22)}; font-weight:800; color:#fff; margin-bottom:${px(size,14)};">
                    <i class="bi bi-gear-fill" style="color:${GOLD}; font-size:${px(size,19)};"></i> Equipamento
                </div>
                ${data.equipment.map(e => `
                    <div style="display:flex; align-items:center; gap:${px(size,10)}; font-size:${px(size,18)}; color:#ededed; margin-bottom:${px(size,10)};">
                        <span style="width:${px(size,7)}; height:${px(size,7)}; border-radius:50%; background:${GOLD}; flex-shrink:0;"></span>${e}
                    </div>`).join('')}
                <div style="margin-top:${px(size,10)};">${goldDividerV2(size, 470)}</div>
            </div>` : '';

        return `
            <div style="width:${size}px; height:${size}px; position:relative; font-family:'Inter',-apple-system,'Helvetica Neue',Arial,sans-serif; color:#fff; ${fullBackgroundStyleV2(data, 'left')} overflow:hidden;">
                <div style="position:absolute; left:${px(size,48)}; top:${px(size,270)}; width:${px(size,620)};">
                    <div style="font-size:${px(size,52)}; font-weight:800; line-height:1.1; margin-bottom:${px(size,6)};">Recomendação</div>
                    <div style="font-size:${px(size,38)}; font-weight:800; color:${GOLD}; line-height:1.15; margin-bottom:${px(size,32)};">${title}</div>
                    ${featureRows}
                    ${equipmentHtml}
                </div>
                ${headerOverlayHtml(size, '1/2')}
            </div>`;
    }

    function slide2HtmlV2(size, data) {
        const price = fmtEuro(data.price);
        const savings = fmtEuro(data.savings);

        return `
            <div style="width:${size}px; height:${size}px; position:relative; font-family:'Inter',-apple-system,'Helvetica Neue',Arial,sans-serif; color:#fff; ${fullBackgroundStyleV2(data, 'right')} overflow:hidden;">
                <div style="position:absolute; right:${px(size,48)}; top:${px(size,400)}; width:${px(size,520)};">
                    ${price ? `
                    <div style="font-size:${px(size,24)}; font-weight:800; color:#fff; margin-bottom:${px(size,6)};">Preço chave na mão</div>
                    <div style="font-size:${px(size,56)}; font-weight:800; color:#fff; margin-bottom:${px(size,22)}; line-height:1.1;">${price}</div>
                    <div style="margin-bottom:${px(size,26)};">${goldDividerV2(size, 520)}</div>` : ''}
                    ${savings ? `
                    <div style="font-size:${px(size,24)}; font-weight:800; color:#fff; margin-bottom:${px(size,6)};">Poupança estimada</div>
                    <div style="font-size:${px(size,56)}; font-weight:800; color:#4ade80; line-height:1.1;">${savings}</div>` : ''}
                </div>
                <div style="position:absolute; left:${px(size,48)}; right:${px(size,48)}; bottom:${px(size,48)}; box-sizing:border-box; background:linear-gradient(135deg, #6e0707 0%, #990000 100%); border-radius:${px(size,16)}; padding:${px(size,18)} ${px(size,20)}; text-align:center; box-shadow:0 ${px(size,14)} ${px(size,34)} rgba(110,7,7,0.4);">
                    <div style="font-size:${px(size,18)}; font-weight:800; margin-bottom:${px(size,4)};">Simular a minha importação</div>
                    <div style="font-size:${px(size,15)}; font-weight:700; color:#ffd8d8;">izzycar.pt →</div>
                </div>
                ${headerOverlayHtml(size, '2/2')}
            </div>`;
    }

    function renderAll() {
        const data = getFormData();

        document.getElementById('previewFrame1').innerHTML = slide1Html(340, data);
        document.getElementById('previewFrame2').innerHTML = slide2Html(340, data);
        document.getElementById('exportFrame1').innerHTML = slide1Html(1080, data);
        document.getElementById('exportFrame2').innerHTML = slide2Html(1080, data);

        document.getElementById('previewFrame1V2').innerHTML = slide1HtmlV2(340, data);
        document.getElementById('previewFrame2V2').innerHTML = slide2HtmlV2(340, data);
        document.getElementById('exportFrame1V2').innerHTML = slide1HtmlV2(1080, data);
        document.getElementById('exportFrame2V2').innerHTML = slide2HtmlV2(1080, data);
    }

    document.querySelectorAll('#f_brand, #f_model, #f_version, #f_mileage, #f_power, #f_fuel, #f_year, #f_equipment, #f_price, #f_savings, #f_url')
        .forEach(el => el.addEventListener('input', renderAll));

    document.getElementById('f_image').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (evt) {
            state.imageDataUrl = evt.target.result;
            renderAll();
        };
        reader.readAsDataURL(file);
    });

    function downloadNode(nodeId, filename) {
        const node = document.getElementById(nodeId).firstElementChild;
        return html2canvas(node, { width: 1080, height: 1080, useCORS: true }).then(canvas => {
            const link = document.createElement('a');
            link.download = filename;
            link.href = canvas.toDataURL('image/png');
            link.click();
        });
    }

    document.getElementById('downloadBtn1').addEventListener('click', () => downloadNode('exportFrame1', 'slide-01.png'));
    document.getElementById('downloadBtn2').addEventListener('click', () => downloadNode('exportFrame2', 'slide-02.png'));
    document.getElementById('downloadBothBtn').addEventListener('click', async () => {
        await downloadNode('exportFrame1', 'slide-01.png');
        await downloadNode('exportFrame2', 'slide-02.png');
    });

    document.getElementById('downloadBtn1V2').addEventListener('click', () => downloadNode('exportFrame1V2', 'slide-01-editorial.png'));
    document.getElementById('downloadBtn2V2').addEventListener('click', () => downloadNode('exportFrame2V2', 'slide-02-editorial.png'));
    document.getElementById('downloadBothBtnV2').addEventListener('click', async () => {
        await downloadNode('exportFrame1V2', 'slide-01-editorial.png');
        await downloadNode('exportFrame2V2', 'slide-02-editorial.png');
    });

    renderAll();
})();
</script>

@endsection
