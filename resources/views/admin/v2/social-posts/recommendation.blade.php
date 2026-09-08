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
        <form method="POST" action="{{ route('admin.v2.social-posts.store') }}" enctype="multipart/form-data" id="postForm">
            @csrf
            @if(isset($post))
            <input type="hidden" name="id" value="{{ $post->id }}">
            @endif
            <input type="hidden" name="remove_gallery_photos" id="f_remove_gallery_photos" value="[]">
            <input type="hidden" name="gallery_layouts" id="f_gallery_layouts" value="{}">
            <input type="hidden" name="gallery_order" id="f_gallery_order" value="[]">
            <input type="hidden" name="gallery_photos_per_slide" id="f_photos_per_slide_hidden" value="{{ old('gallery_photos_per_slide', optional($post)->gallery_photos_per_slide ?: 3) }}">
            <input type="file" name="gallery_photos[]" id="f_gallery" multiple accept="image/*" style="display:none">
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

                    <div class="col-12">
                        <label class="form-label">Mais fotos do carro <span class="text-muted">(até 12 fotos)</span></label>
                        <input type="file" id="f_gallery_visible" class="form-control" accept="image/*" multiple>
                        <div class="form-text">Arrasta as fotos para as reordenar. Escolhe abaixo quantas fotos aparecem em cada slide extra ("Fotos do Carro").</div>

                        <div class="mt-2" style="max-width:260px;">
                            <select id="f_photos_per_slide" class="form-select form-select-sm">
                                <option value="3">3 fotos por slide (com disposição)</option>
                                <option value="1">1 foto por slide (destaque)</option>
                            </select>
                        </div>

                        <div id="galleryThumbs" class="d-flex flex-wrap gap-2 mt-2"></div>
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

        <div class="modern-card mt-4">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-images"></i>
                    Fotos do Carro <span class="text-muted small">(3 por slide)</span>
                </h5>
            </div>

            <div id="gallerySlidesContainer">
                <p class="text-muted text-center py-4 mb-0">Adiciona fotos no formulário para gerar os slides.</p>
            </div>
        </div>
    </div>
</div>

<!-- Alvos de exportação, fora do ecrã, sempre ao tamanho real (1080x1080) -->
<div style="position:absolute; left:-99999px; top:0;" class="export-offscreen">
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
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script>
(function () {
    const LOGO_URL = '{{ asset('img/logo-transparente.png') }}';
    const GOLD = '#d9b25c';

    const state = {
        imageDataUrl: @json(isset($post) && $post->image ? Illuminate\Support\Facades\Storage::url($post->image) : null),
        imageNaturalW: null,
        imageNaturalH: null,
        gallery: @json(isset($post) && $post->gallery_photos ? collect($post->gallery_photos)->map(fn ($p) => ['url' => Illuminate\Support\Facades\Storage::url($p), 'isNew' => false, 'existingPath' => $p])->values() : []),
        galleryLayouts: @json(optional($post)->gallery_layouts ?: (object) []),
        photosPerSlide: {{ (int) (optional($post)->gallery_photos_per_slide ?: 3) }},
    };
    let removedExistingGalleryPaths = [];

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

    /**
     * O "truque" de mostrar metade da foto por slide usava background-size
     * fixo (200% 100%), o que ESTICA a imagem sempre que a proporção real
     * não é exatamente 2:1 — ficando disforme. Em vez disso, medimos as
     * dimensões reais da imagem e escalamos só pela altura (sem distorcer),
     * cortando a largura que sobra — tal como um "object-fit:cover" a dobrar
     * a largura do slide.
     */
    function imageCoverWidthPercent() {
        if (!state.imageNaturalW || !state.imageNaturalH) return 200;
        const scaledWidth = state.imageNaturalW * (1080 / state.imageNaturalH);
        return Math.max((scaledWidth / 1080) * 100, 100).toFixed(2);
    }

    function measureHeroImage(url) {
        if (!url) {
            state.imageNaturalW = null;
            state.imageNaturalH = null;
            return;
        }
        const img = new Image();
        img.onload = function () {
            state.imageNaturalW = img.naturalWidth;
            state.imageNaturalH = img.naturalHeight;
            renderAll();
        };
        img.onerror = function () {
            state.imageNaturalW = null;
            state.imageNaturalH = null;
        };
        img.src = url;
    }

    function fullBackgroundStyle(data, side) {
        if (!data.image) {
            return `background: radial-gradient(120% 140% at 50% 0%, #241c14 0%, #14100c 60%, #0b0906 100%);`;
        }
        const position = side === 'left' ? 'left center' : 'right center';
        return `background-image: linear-gradient(180deg, rgba(5,4,3,0.35) 0%, rgba(5,4,3,0.05) 25%, rgba(5,4,3,0.1) 55%, rgba(5,4,3,0.5) 100%), url('${data.image}');
                background-size: 100% 100%, ${imageCoverWidthPercent()}% 100%;
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
                ${pageLabel ? `<span style="font-size:${px(size,13)}; font-weight:700; color:#fff; background:rgba(255,255,255,0.14); border-radius:999px; padding:${px(size,4)} ${px(size,12)};">${pageLabel}</span>` : ''}
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
                background-size: 100% 100%, 100% 100%, ${imageCoverWidthPercent()}% 100%;
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
                    <div style="font-size:${px(size,38)}; font-weight:800; color:${GOLD}; line-height:1.15; margin-bottom:${px(size,8)};">${title}</div>
                    ${data.version ? `<div style="font-size:${px(size,18)}; color:#cfcfcf; margin-bottom:${px(size,24)};">${data.version}</div>` : `<div style="margin-bottom:${px(size,24)};"></div>`}
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

    /**
     * "Fotos do Carro": slides extra com 3 fotos cada, disposição escolhida
     * pelo utilizador por slide (uma das 4 abaixo). Cada foto é um <img
     * object-fit:cover> absolutamente posicionado — sem canvas, mesma
     * abordagem do resto do ficheiro.
     */
    const GALLERY_LAYOUTS = {
        'hero-duo': 'Foto grande + 2 em baixo',
        'stripes': 'Faixas horizontais',
        'triptych': 'Tríptico vertical',
        'big-stack': 'Grande + 2 empilhadas',
    };
    const GALLERY_LAYOUT_ORDER = Object.keys(GALLERY_LAYOUTS);
    const GALLERY_GAP = 8;

    function photoTile(size, url, top, left, width, height) {
        return `<div style="position:absolute; top:${px(size,top)}; left:${px(size,left)}; width:${px(size,width)}; height:${px(size,height)}; overflow:hidden; background:#1a1512;">
            <img src="${url}" style="width:100%; height:100%; object-fit:cover; display:block;">
        </div>`;
    }

    function photoGridHtml(size, photos, layout) {
        const g = GALLERY_GAP;
        if (photos.length <= 1) {
            return photos[0] ? photoTile(size, photos[0], 0, 0, 1080, 1080) : '';
        }
        if (photos.length === 2) {
            const w = (1080 - g) / 2;
            return photoTile(size, photos[0], 0, 0, w, 1080) + photoTile(size, photos[1], 0, w + g, w, 1080);
        }
        switch (layout) {
            case 'stripes': {
                const h = (1080 - 2 * g) / 3;
                return photoTile(size, photos[0], 0, 0, 1080, h)
                     + photoTile(size, photos[1], h + g, 0, 1080, h)
                     + photoTile(size, photos[2], 2 * (h + g), 0, 1080, h);
            }
            case 'triptych': {
                const w = (1080 - 2 * g) / 3;
                return photoTile(size, photos[0], 0, 0, w, 1080)
                     + photoTile(size, photos[1], 0, w + g, w, 1080)
                     + photoTile(size, photos[2], 0, 2 * (w + g), w, 1080);
            }
            case 'big-stack': {
                const wBig = 1080 * 0.6 - g / 2;
                const wSmall = 1080 - wBig - g;
                const hSmall = (1080 - g) / 2;
                return photoTile(size, photos[0], 0, 0, wBig, 1080)
                     + photoTile(size, photos[1], 0, wBig + g, wSmall, hSmall)
                     + photoTile(size, photos[2], hSmall + g, wBig + g, wSmall, hSmall);
            }
            case 'hero-duo':
            default: {
                const hBig = 1080 * 0.6 - g / 2;
                const hSmall = 1080 - hBig - g;
                const wSmall = (1080 - g) / 2;
                return photoTile(size, photos[0], 0, 0, 1080, hBig)
                     + photoTile(size, photos[1], hBig + g, 0, wSmall, hSmall)
                     + photoTile(size, photos[2], hBig + g, wSmall + g, wSmall, hSmall);
            }
        }
    }

    function gallerySlideHtml(size, data, photos, layout, pageLabel) {
        const title = [data.brand, data.model].filter(Boolean).join(' ');
        return `
            <div style="width:${size}px; height:${size}px; position:relative; font-family:'Inter',-apple-system,'Helvetica Neue',Arial,sans-serif; color:#fff; background:#0b0906; overflow:hidden;">
                ${photoGridHtml(size, photos, layout)}
                <div style="position:absolute; inset:0; pointer-events:none; background: linear-gradient(180deg, transparent 78%, rgba(0,0,0,0.6) 100%);"></div>
                ${title ? `<div style="position:absolute; left:${px(size,44)}; bottom:${px(size,26)}; font-size:${px(size,20)}; font-weight:800;">${title}</div>` : ''}
                ${headerOverlayHtml(size, pageLabel)}
            </div>`;
    }

    function getGalleryPhotoUrls() {
        return state.gallery.map(g => g.url);
    }

    let galleryThumbsSortable = null;

    function renderGalleryThumbs() {
        const wrap = document.getElementById('galleryThumbs');
        wrap.innerHTML = state.gallery.map((g, i) => `
            <div style="position:relative; width:70px; height:70px; cursor:grab;">
                <img src="${g.url}" style="width:100%; height:100%; object-fit:cover; border-radius:8px; border:1px solid #ddd; pointer-events:none;">
                <button type="button" class="btn btn-sm btn-danger" style="position:absolute; top:-6px; right:-6px; width:22px; height:22px; padding:0; line-height:1; border-radius:50%;" onclick="window.__removeGalleryPhoto(${i})">×</button>
            </div>`).join('');

        if (!galleryThumbsSortable && window.Sortable) {
            galleryThumbsSortable = Sortable.create(wrap, {
                animation: 150,
                forceFallback: true,
                onEnd: function (evt) {
                    const [moved] = state.gallery.splice(evt.oldIndex, 1);
                    state.gallery.splice(evt.newIndex, 0, moved);
                    renderGalleryThumbs();
                    renderGallerySection();
                },
            });
        }
    }

    function renderGallerySection() {
        const data = getFormData();
        const photos = getGalleryPhotoUrls();
        const container = document.getElementById('gallerySlidesContainer');

        if (photos.length === 0) {
            container.innerHTML = '<p class="text-muted text-center py-4 mb-0">Adiciona fotos no formulário para gerar os slides.</p>';
            return;
        }

        const perSlide = state.photosPerSlide || 3;
        const slideCount = Math.max(1, Math.ceil(photos.length / perSlide));
        let html = '<div class="d-flex flex-wrap gap-4 justify-content-center">';
        for (let i = 1; i <= slideCount; i++) {
            const slidePhotos = photos.slice((i - 1) * perSlide, (i - 1) * perSlide + perSlide);
            const layout = state.galleryLayouts[i] || GALLERY_LAYOUT_ORDER[(i - 1) % GALLERY_LAYOUT_ORDER.length];
            const pageLabel = slideCount > 1 ? `${i}/${slideCount}` : '';
            html += `
                <div class="text-center">
                    <div class="post-preview-frame">${gallerySlideHtml(340, data, slidePhotos, layout, pageLabel)}</div>
                    ${slidePhotos.length === 3 ? `
                    <select class="form-select form-select-sm mt-2" onchange="window.__setGalleryLayout(${i}, this.value)">
                        ${GALLERY_LAYOUT_ORDER.map(key => `<option value="${key}" ${layout === key ? 'selected' : ''}>${GALLERY_LAYOUTS[key]}</option>`).join('')}
                    </select>` : ''}
                    <button type="button" class="btn btn-outline-secondary btn-sm mt-2" onclick="window.__downloadGallerySlide(${i})">
                        <i class="bi bi-download"></i> Descarregar Slide ${i}
                    </button>
                </div>`;
        }
        html += '</div><div class="text-center mt-4"><button type="button" class="btn btn-primary" onclick="window.__downloadAllGallerySlides()"><i class="bi bi-download"></i> Descarregar todos os slides</button></div>';
        container.innerHTML = html;
    }

    window.__removeGalleryPhoto = function (index) {
        const [removed] = state.gallery.splice(index, 1);
        if (removed && removed.existingPath) {
            removedExistingGalleryPaths.push(removed.existingPath);
        }
        renderGalleryThumbs();
        renderGallerySection();
    };

    window.__setGalleryLayout = function (slideIndex, layout) {
        state.galleryLayouts[slideIndex] = layout;
        renderGallerySection();
    };

    function downloadHtmlString(html1080, filename) {
        const wrapper = document.querySelector('.export-offscreen');
        const temp = document.createElement('div');
        temp.innerHTML = html1080;
        wrapper.appendChild(temp);
        return html2canvas(temp.firstElementChild, { width: 1080, height: 1080, useCORS: true }).then(canvas => {
            const link = document.createElement('a');
            link.download = filename;
            link.href = canvas.toDataURL('image/png');
            link.click();
        }).finally(() => wrapper.removeChild(temp));
    }

    window.__downloadGallerySlide = function (slideIndex) {
        const data = getFormData();
        const photos = getGalleryPhotoUrls();
        const perSlide = state.photosPerSlide || 3;
        const slideCount = Math.max(1, Math.ceil(photos.length / perSlide));
        const slidePhotos = photos.slice((slideIndex - 1) * perSlide, (slideIndex - 1) * perSlide + perSlide);
        const layout = state.galleryLayouts[slideIndex] || GALLERY_LAYOUT_ORDER[(slideIndex - 1) % GALLERY_LAYOUT_ORDER.length];
        const pageLabel = slideCount > 1 ? `${slideIndex}/${slideCount}` : '';
        const html = gallerySlideHtml(1080, data, slidePhotos, layout, pageLabel);
        return downloadHtmlString(html, `slide-fotos-${String(slideIndex).padStart(2, '0')}.png`);
    };

    window.__downloadAllGallerySlides = async function () {
        const photos = getGalleryPhotoUrls();
        const perSlide = state.photosPerSlide || 3;
        const slideCount = Math.max(1, Math.ceil(photos.length / perSlide));
        for (let i = 1; i <= slideCount; i++) {
            await window.__downloadGallerySlide(i);
        }
    };

    document.getElementById('f_photos_per_slide').value = String(state.photosPerSlide);
    document.getElementById('f_photos_per_slide').addEventListener('change', function () {
        state.photosPerSlide = parseInt(this.value, 10) || 3;
        document.getElementById('f_photos_per_slide_hidden').value = state.photosPerSlide;
        renderGallerySection();
    });

    document.getElementById('f_gallery_visible').addEventListener('change', function (e) {
        const files = Array.from(e.target.files);
        this.value = '';
        const remainingSlots = 12 - state.gallery.length;
        files.slice(0, remainingSlots).forEach(file => {
            const reader = new FileReader();
            reader.onload = function (evt) {
                state.gallery.push({ url: evt.target.result, isNew: true, existingPath: null, file });
                renderGalleryThumbs();
                renderGallerySection();
            };
            reader.readAsDataURL(file);
        });
    });

    document.getElementById('postForm').addEventListener('submit', function () {
        const dt = new DataTransfer();
        const order = [];
        let newIndex = 0;
        state.gallery.forEach(g => {
            if (g.isNew) {
                dt.items.add(g.file);
                order.push('new:' + newIndex);
                newIndex++;
            } else {
                order.push('existing:' + g.existingPath);
            }
        });
        document.getElementById('f_gallery').files = dt.files;
        document.getElementById('f_gallery_order').value = JSON.stringify(order);
        document.getElementById('f_remove_gallery_photos').value = JSON.stringify(removedExistingGalleryPaths);
        document.getElementById('f_gallery_layouts').value = JSON.stringify(state.galleryLayouts);
    });

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

        renderGallerySection();
    }

    document.querySelectorAll('#f_brand, #f_model, #f_version, #f_mileage, #f_power, #f_fuel, #f_year, #f_equipment, #f_price, #f_savings, #f_url')
        .forEach(el => el.addEventListener('input', renderAll));

    document.getElementById('f_image').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (evt) {
            state.imageDataUrl = evt.target.result;
            measureHeroImage(state.imageDataUrl);
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

    renderGalleryThumbs();
    measureHeroImage(state.imageDataUrl);
    renderAll();
})();
</script>

@endsection
