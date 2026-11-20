@extends('layouts.admin-v2')

@section('title', 'Editor — ' . ($post->title ?: 'Publicação'))

@section('content')

@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
['icon' => 'bi bi-stars', 'label' => 'Marketing Studio', 'href' => route('admin.marketing.index')],
['icon' => 'bi bi-pencil', 'label' => 'Editor', 'href' => ''],
],
'title' => $post->title ?: 'Publicação',
'subtitle' => $post->type->label() . ' · ' . $post->status->label(),
])

@include('admin.marketing._styles')

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

@if($sourceLost)
<div class="alert alert-danger"><i class="bi bi-shield-exclamation"></i> <b>{{ $sourceLost }}</b> A exportação e a legenda estão bloqueadas; elimina este post ou volta a marcar a autorização.</div>
@endif
@if($legalCheck)
<div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> <b>Verificar a legislação atual antes de publicar.</b> Este tema é fiscal/legal e o texto é teu: confirma valores, prazos e condições nas fontes oficiais. Ao exportar vais ter de confirmar.</div>
@endif

<div class="alert alert-warning d-lg-none">Para exportar imagens com a qualidade correta, usa o computador.</div>

<div class="row g-4" id="studio" data-update-url="{{ route('admin.marketing.update', $post) }}" data-preview-url="{{ route('admin.marketing.preview', $post) }}" data-caption-url="{{ route('admin.marketing.caption', $post) }}">

    {{-- ═══ Controlos ═══ --}}
    <div class="col-lg-5 col-xl-4">
        <div class="card mb-3"><div class="card-header fw-semibold">Template e formato</div><div class="card-body">
            <label class="form-label small">Template</label>
            <select id="f_template" class="form-select mb-3">
                @foreach($templates as $t)
                    @php $bloq = ($t->key === 'vendido' && ($post->data['status'] ?? null) !== 'vendido') || ($t->key === 'novo-stock' && ($post->data['status'] ?? null) !== 'em_stock'); @endphp
                    <option value="{{ $t->key }}" data-formats='@json(array_map(fn ($f) => $f->value, $t->formats))' data-max-items="{{ $t->maxItems }}" @selected($post->template === $t->key) @disabled($bloq && $post->template !== $t->key)>{{ $t->name }}</option>
                @endforeach
            </select>
            <label class="form-label small">Formato</label>
            <select id="f_format" class="form-select">
                @foreach($formats as $f)<option value="{{ $f->value }}" @selected($post->format === $f)>{{ $f->label() }}</option>@endforeach
            </select>
        </div></div>

        <div class="card mb-3"><div class="card-header fw-semibold">Texto da imagem</div><div class="card-body">
            @if($kind === 'quote')
            <label class="form-label small">Texto na imagem (excerto exato do comentário)</label>
            <textarea id="o_quote" rows="5" maxlength="400" class="form-control mb-2">{{ $overrides['quote'] ?? $defaults['quote'] }}</textarea>
            <div class="form-text mb-2">Não é possível reescrever: só apagar partes no início ou no fim. @if(($defaults['quote'] ?? '') === '')<b>O comentário é longo: escolhe um excerto.</b>@endif</div>
            <details class="mb-3"><summary class="small">Comentário original</summary><div class="small text-muted mt-1" style="white-space:pre-line">{{ $post->data['comment'] ?? '' }}</div></details>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="o_fullname" @checked($overrides['show_full_name'] ?? false)>
                <label class="form-check-label small" for="o_fullname">Mostrar o nome completo (por omissão só o primeiro nome)</label>
            </div>
            @else
            <label class="form-label small">Título</label>
            <input id="o_title" class="form-control mb-2" maxlength="120" value="{{ $overrides['title'] ?? $defaults['title'] }}">
            <label class="form-label small">Subtítulo</label>
            <input id="o_subtitle" class="form-control mb-2" maxlength="160" value="{{ $overrides['subtitle'] ?? $defaults['subtitle'] }}">
            @endif
            @if($kind === 'text')
            <label class="form-label small">Conteúdo @if($topicHint)<span class="text-muted">— {{ $topicHint }}</span>@endif</label>
            <div id="itemsList" class="mb-2"></div>
            <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="btnAddItem"><i class="bi bi-plus-lg"></i> Acrescentar</button>
            <div class="form-text mb-3">Cada linha tem duas partes: a primeira (a) e a segunda (b). O significado depende do template: passo e detalhe, mito e verdade, pergunta e resposta.</div>
            @elseif($kind !== 'quote')
            <div class="row g-2 mb-2">
                @if($kind === 'vehicle')
                <div class="col-6">
                    <label class="form-label small">Preço (€)</label>
                    <input id="o_price" type="number" step="1" min="0" class="form-control" value="{{ array_key_exists('price', $overrides) ? $overrides['price'] : $defaults['price'] }}">
                </div>
                @endif
                @if(in_array($post->type, [\App\Enums\MarketingPostType::Recomendacao, \App\Enums\MarketingPostType::Importacao], true))
                <div class="col-6">
                    <label class="form-label small">Poupança (€)</label>
                    <input id="o_savings" type="number" step="1" min="0" class="form-control" value="{{ array_key_exists('savings', $overrides) ? $overrides['savings'] : $defaults['savings'] }}">
                </div>
                @endif
            </div>
            <div id="priceWarn" class="small text-warning mb-2 {{ $priceDiffers ? '' : 'd-none' }}"><i class="bi bi-exclamation-triangle"></i> O preço difere do catálogo ({{ $post->data['price'] ?? null ? number_format($post->data['price'], 0, ',', '.') . ' €' : 'sem preço' }}).</div>
            <label class="form-label small">Destaques (um por linha, máx. 6)</label>
            <textarea id="o_highlights" rows="4" class="form-control mb-2">{{ implode("\n", $overrides['highlights'] ?? $defaults['highlights']) }}</textarea>
            @endif
            <label class="form-label small">Chamada à ação (na imagem)</label>
            <input id="o_cta" class="form-control mb-2" maxlength="120" value="{{ $overrides['cta'] ?? $defaults['cta'] }}">
            @if(! in_array($kind, ['text', 'quote'], true))
            <div id="badgeBox">
                <label class="form-label small">Etiqueta (ex.: Vendido / Entregue)</label>
                <input id="o_badge" class="form-control" maxlength="40" placeholder="Por omissão do template" value="{{ $overrides['badge'] ?? '' }}">
            </div>
            @endif
        </div></div>

        @if(! in_array($kind, ['text', 'quote'], true))
        <div class="card mb-3"><div class="card-header fw-semibold">Fotografias</div><div class="card-body">
            @if($photoCount === 0)
                <div class="alert alert-warning mb-0">Este post não tem fotografias. O template mostra um bloco neutro; carrega fotos na viatura e usa "Atualizar dados".</div>
            @else
            <p class="small text-muted">Clica para escolher e ordenar (1.ª = principal). Duplo clique numa foto escolhida define o ponto focal no ponto onde clicares.</p>
            <div id="photoGrid" class="d-flex flex-wrap gap-2"></div>
            <div id="perSlideBox" class="mt-3 d-none">
                <label class="form-label small">Fotos por slide (carrossel)</label>
                <select id="f_perslide" class="form-select form-select-sm">
                    <option value="1" @selected(($config['gallery_photos_per_slide'] ?? 1) == 1)>1 foto</option>
                    <option value="3" @selected(($config['gallery_photos_per_slide'] ?? 1) == 3)>3 fotos (grelha)</option>
                </select>
            </div>
            @endif
        </div></div>
        @endif

        <div class="card mb-3"><div class="card-header fw-semibold">Legenda</div><div class="card-body">
            <div class="d-flex gap-2 mb-2">
                <select id="f_tone" class="form-select form-select-sm" aria-label="Tom da legenda">
                    @foreach(\App\Services\MarketingCaptionService::TONES as $k => $desc)<option value="{{ $k }}" title="{{ $desc }}">{{ ucfirst($k === 'descontraido' ? 'descontraído' : $k) }}</option>@endforeach
                </select>
                <button type="button" class="btn btn-sm btn-outline-primary text-nowrap" id="btnCaption"><i class="bi bi-stars"></i> Gerar legenda</button>
            </div>
            <div class="form-text mb-2">{{ $aiEnabled ? 'Sugestão com IA a partir dos dados do post; revê sempre antes de publicar.' : 'Sem IA configurada: a sugestão é montada com os dados do post.' }}</div>
            <div id="captionMsg" class="small text-warning mb-2"></div>
            <textarea id="f_caption" rows="6" class="form-control mb-2" placeholder="Escreve a legenda da publicação…">{{ $post->caption }}</textarea>
            <input id="f_hashtags" class="form-control mb-2" placeholder="#izzycar #importacaoautomovel" value="{{ $post->hashtags }}">
            <input id="f_cta" class="form-control mb-2" placeholder="Chamada à ação da legenda" value="{{ $post->cta }}">
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCopy"><i class="bi bi-clipboard"></i> Copiar legenda</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnTxt"><i class="bi bi-download"></i> .txt</button>
            </div>
        </div></div>

        <div class="card mb-3"><div class="card-header fw-semibold">Agendamento</div><div class="card-body">
            <label class="form-label small" for="f_when">Data e hora planeadas</label>
            <input type="datetime-local" id="f_when" class="form-control mb-2" value="{{ $post->scheduled_for?->format('Y-m-d\TH:i') }}">
            <label class="form-label small" for="f_channel">Rede</label>
            <select id="f_channel" class="form-select">
                <option value="">Sem rede</option>
                @foreach(['instagram' => 'Instagram', 'facebook' => 'Facebook', 'ambos' => 'Instagram e Facebook'] as $k => $l)<option value="{{ $k }}" @selected($post->channel === $k)>{{ $l }}</option>@endforeach
            </select>
            <div class="form-text">Só planeamento no calendário: o Studio não publica. Um post aprovado com data fica "Agendado". <a href="{{ route('admin.marketing.calendar', ['date' => ($post->scheduled_for ?? now())->toDateString()]) }}">Ver calendário</a></div>
        </div></div>

        <div class="card mb-3"><div class="card-header fw-semibold">Estado</div><div class="card-body">
            <select id="f_status" class="form-select">
                @foreach([\App\Enums\MarketingPostStatus::Rascunho, \App\Enums\MarketingPostStatus::EmRevisao, \App\Enums\MarketingPostStatus::Aprovado] as $st)
                    <option value="{{ $st->value }}" @selected($post->status === $st)>{{ $st->label() }}</option>
                @endforeach
                @if(in_array($post->status, [\App\Enums\MarketingPostStatus::Agendado, \App\Enums\MarketingPostStatus::Publicado], true))
                    <option value="" selected disabled>{{ $post->status->label() }}</option>
                @endif
            </select>
            <div class="form-text">"Publicado" só se marca com o botão abaixo, depois de publicares mesmo.</div>
        </div></div>
    </div>

    {{-- ═══ Pré-visualização ═══ --}}
    <div class="col-lg-7 col-xl-8">
        <div class="d-flex flex-wrap gap-2 mb-3 sticky-top bg-body py-2" style="z-index:5;top:70px">
            <button class="btn btn-primary" id="btnSave"><i class="bi bi-save"></i> Guardar</button>
            <button class="btn btn-outline-primary" id="btnPng"><i class="bi bi-filetype-png"></i> PNG</button>
            <button class="btn btn-outline-primary" id="btnJpg"><i class="bi bi-filetype-jpg"></i> JPG</button>
            <button class="btn btn-outline-primary d-none" id="btnZip"><i class="bi bi-file-zip"></i> ZIP (todos os slides)</button>
            <span id="saveMsg" class="small text-muted align-self-center"></span>
            <div class="ms-auto d-flex gap-2">
                <form method="POST" action="{{ route('admin.marketing.refresh', $post) }}" onsubmit="return confirm('Substituir os dados do post pelos dados atuais da fonte (viatura ou testemunho)? O título, a legenda e as tuas edições mantêm-se.')">@csrf
                    <button class="btn btn-outline-secondary" @disabled(! $post->source_type)><i class="bi bi-arrow-repeat"></i> Atualizar dados</button></form>
                <form method="POST" action="{{ route('admin.marketing.duplicate', $post) }}">@csrf<button class="btn btn-outline-secondary"><i class="bi bi-files"></i> Duplicar</button></form>
                <form method="POST" action="{{ route('admin.marketing.mark-published', $post) }}" onsubmit="return confirm('Confirmas que esta publicação já foi publicada nas redes sociais?')">@csrf<button class="btn btn-outline-success"><i class="bi bi-check2-circle"></i> Marcar como publicado</button></form>
                <form method="POST" action="{{ route('admin.marketing.destroy', $post) }}" onsubmit="return confirm('Eliminar esta publicação?')">@csrf @method('DELETE')<button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button></form>
            </div>
        </div>
        <div id="overflowWarn" class="alert alert-warning d-none w-100"><i class="bi bi-exclamation-triangle"></i> O texto não cabe na imagem e ficaria cortado. Encurta-o para poderes exportar.</div>
        <div id="previewFrames" class="d-flex flex-wrap gap-3"></div>
    </div>
</div>

{{-- Palco de exportação: mesmo HTML, tamanho real, fora do ecrã. --}}
<div id="exportStage" style="position:fixed;left:-20000px;top:0;pointer-events:none">{!! $preview['html'] !!}</div>

@include('admin.marketing._editor-scripts')
@endsection
