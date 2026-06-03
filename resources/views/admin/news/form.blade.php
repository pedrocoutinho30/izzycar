@extends('layouts.admin-v2')
@section('title', isset($article) ? 'Editar Artigo' : 'Novo Artigo')

@push('styles')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/super-build/ckeditor.js"></script>
<style>
.af-label { display:block; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#374151; margin-bottom:.4rem; }
.af-req   { color:#6e0707; }
.af-char  { font-size:.72rem; color:#9ca3af; text-align:right; margin-top:.2rem; }
.af-wc    { font-size:.72rem; color:#9ca3af; font-weight:500; }

.af-status { display:grid; grid-template-columns:1fr 1fr; gap:.5rem; }
.af-status input[type=radio] { display:none; }
.af-status__opt { display:flex; align-items:center; justify-content:center; gap:.4rem; padding:.65rem; border-radius:10px; border:2px solid #e5e7eb; cursor:pointer; font-size:.82rem; font-weight:600; color:#6b7280; transition:.15s; }
.af-status__opt:hover { background:#f9fafb; }
.af-status input:checked + .af-status__opt--pub   { border-color:#16a34a; background:#f0fdf4; color:#16a34a; }
.af-status input:checked + .af-status__opt--draft { border-color:#ca8a04; background:#fefce8; color:#ca8a04; }

.af-cover-wrap  { position:relative; border-radius:10px; overflow:hidden; border:1px solid #e5e7eb; }
.af-cover-img   { width:100%; display:block; max-height:200px; object-fit:cover; }
.af-remove-btn  { position:absolute; top:.4rem; right:.4rem; width:26px; height:26px; border-radius:7px; background:rgba(0,0,0,.65); color:#fff; border:none; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:.7rem; transition:.2s; }
.af-remove-btn:hover { background:rgba(220,38,38,.9); }

.af-drop { border:2px dashed #e5e7eb; border-radius:12px; padding:1.5rem 1rem; text-align:center; position:relative; cursor:pointer; transition:.2s; }
.af-drop:hover, .af-drop--over { border-color:#6e0707; background:#fdf2f8; }
.af-drop__input { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
.af-drop__icon  { font-size:1.75rem; color:#9ca3af; display:block; margin-bottom:.35rem; }
.af-drop__text  { font-size:.85rem; font-weight:600; color:#374151; margin:0; }
.af-drop__hint  { font-size:.7rem; color:#9ca3af; margin:.1rem 0 0; }

.af-gallery { display:grid; grid-template-columns:repeat(auto-fill, minmax(80px,1fr)); gap:.5rem; }
.af-gallery__item { position:relative; aspect-ratio:1; border-radius:8px; overflow:hidden; border:1px solid #e5e7eb; }
.af-gallery__item img { width:100%; height:100%; object-fit:cover; display:block; }
.af-gallery__remove { position:absolute; top:.2rem; right:.2rem; width:22px; height:22px; border-radius:6px; background:rgba(0,0,0,.65); color:#fff; border:none; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:.65rem; transition:.2s; }
.af-gallery__remove:hover { background:rgba(220,38,38,.9); }

.ck.ck-editor__editable { min-height:130px !important; }
</style>
@endpush

@section('content')
<div class="admin-content">

@php $isEdit = isset($article); @endphp

@include('components.admin.page-header', [
  'breadcrumbs' => [
    ['icon' => 'bi bi-house-door', 'label' => 'Dashboard',  'href' => route('admin.v2.dashboard')],
    ['icon' => 'bi bi-newspaper',  'label' => 'Notícias',   'href' => route('admin.news.index')],
    ['icon' => '',                 'label' => $isEdit ? 'Editar' : 'Novo Artigo'],
  ],
  'title'       => $isEdit ? 'Editar Artigo' : 'Novo Artigo',
  'subtitle'    => $isEdit ? $article->title : 'Preenche os campos e publica',
  'actionHref'  => '', 'actionLabel' => '',
])

@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    <button class="btn-close ms-auto" data-bs-dismiss="alert"></button>
  </div>
@endif

@if($errors->any())
  <div class="alert alert-danger alert-dismissible fade show mb-4">
    <strong>Corrige os erros:</strong>
    <ul class="mb-0 mt-1">
      @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
    <button class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

<form action="{{ $isEdit ? route('admin.news.update', $article->id) : route('admin.news.store') }}"
      method="POST" enctype="multipart/form-data" id="articleForm">
  @csrf
  @if($isEdit) @method('PUT') @endif

  <div class="row g-4">

    {{-- ══════════════════ LEFT ══════════════════ --}}
    <div class="col-lg-8">

      {{-- Título --}}
      <div class="modern-card mb-4">
        <div class="modern-card-header">
          <h5 class="modern-card-title"><i class="bi bi-type-h1"></i> Título & URL</h5>
        </div>
        <div class="row g-3">
          <div class="col-12">
            <label class="af-label">Título <span class="af-req">*</span></label>
            <input type="text" name="title" id="afTitle"
                   class="form-control form-control-lg fw-semibold @error('title') is-invalid @enderror"
                   value="{{ old('title', $article->title ?? '') }}"
                   placeholder="Ex: Como calcular o ISV ao importar um carro" required>
            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-12">
            <label class="af-label">URL do artigo</label>
            <div class="input-group">
              <span class="input-group-text text-muted small border-end-0 bg-light">/noticias/</span>
              <input type="text" name="slug" id="afSlug"
                     class="form-control @error('slug') is-invalid @enderror"
                     value="{{ old('slug', $article->slug ?? '') }}"
                     placeholder="url-gerada-automaticamente">
            </div>
            @error('slug')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            <div class="form-text">Gerado automaticamente a partir do título.</div>
          </div>
        </div>
      </div>

      {{-- Subtítulo --}}
      <div class="modern-card mb-4">
        <div class="modern-card-header">
          <h5 class="modern-card-title"><i class="bi bi-blockquote-left"></i> Subtítulo / Introdução</h5>
          <span class="badge bg-light border text-muted small">Aparece em destaque no início</span>
        </div>
        <textarea name="subtitle" id="afSubtitle">{{ old('subtitle', $article->subtitle ?? '') }}</textarea>
      </div>

      {{-- Conteúdo --}}
      <div class="modern-card mb-4">
        <div class="modern-card-header">
          <h5 class="modern-card-title"><i class="bi bi-file-text"></i> Conteúdo Principal</h5>
          <span class="af-wc" id="afWc">— palavras</span>
        </div>
        <textarea name="content" id="afContent"
                  class="@error('content') is-invalid @enderror">{{ old('content', $article->content ?? '') }}</textarea>
        @error('content')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
      </div>

      {{-- Sumário --}}
      <div class="modern-card mb-4">
        <div class="modern-card-header">
          <h5 class="modern-card-title"><i class="bi bi-check2-circle"></i> Sumário / Conclusão</h5>
          <span class="badge bg-light border text-muted small">Caixa de destaque no final</span>
        </div>
        <textarea name="summary" id="afSummary">{{ old('summary', $article->summary ?? '') }}</textarea>
      </div>

      {{-- SEO --}}
      <div class="modern-card mb-4">
        <div class="modern-card-header" style="cursor:pointer"
             data-bs-toggle="collapse" data-bs-target="#seoPanel">
          <h5 class="modern-card-title"><i class="bi bi-search"></i> SEO & Metadados</h5>
          <i class="bi bi-chevron-down ms-auto text-muted"></i>
        </div>
        <div class="collapse {{ $isEdit ? 'show' : '' }}" id="seoPanel">
          <div class="row g-3 pt-2">
            <div class="col-12">
              <label class="af-label">Título SEO</label>
              <input type="text" name="seo_title" id="afSeoTitle" maxlength="80" class="form-control"
                     placeholder="Título para o Google (até 60 caracteres)"
                     value="{{ old('seo_title', $article->seo_title ?? $article->title ?? '') }}">
              <div class="af-char" id="afSeoTitleC">0 / 60</div>
            </div>
            <div class="col-12">
              <label class="af-label">Meta Descrição</label>
              <textarea name="seo_description" id="afSeoDesc" maxlength="200" rows="2" class="form-control"
                        placeholder="Descrição para o Google (120–160 caracteres)">{{ old('seo_description', $article->seo_description ?? '') }}</textarea>
              <div class="af-char" id="afSeoDescC">0 / 160</div>
            </div>
            <div class="col-12">
              <label class="af-label">Palavras-chave</label>
              <input type="text" name="seo_keywords" class="form-control"
                     placeholder="importação automóvel, ISV, ..."
                     value="{{ old('seo_keywords', $article->seo_keywords ?? '') }}">
            </div>
          </div>
        </div>
      </div>

    </div>

    {{-- ══════════════════ RIGHT ══════════════════ --}}
    <div class="col-lg-4">
      <div style="position:sticky; top:100px; display:flex; flex-direction:column; gap:1.25rem">

        {{-- Publicação --}}
        <div class="modern-card">
          <div class="modern-card-header">
            <h5 class="modern-card-title"><i class="bi bi-send"></i> Publicação</h5>
          </div>
          <div class="d-flex flex-column gap-3">

            {{-- Status --}}
            <div>
              <label class="af-label">Estado <span class="af-req">*</span></label>
              <div class="af-status">
                <input type="radio" name="status" value="Publicado" id="stPub"
                       {{ old('status', $article->status ?? 'Rascunho') === 'Publicado' ? 'checked' : '' }}>
                <label for="stPub" class="af-status__opt af-status__opt--pub">
                  <i class="bi bi-check-circle-fill"></i> Publicado
                </label>
                <input type="radio" name="status" value="Rascunho" id="stDraft"
                       {{ old('status', $article->status ?? 'Rascunho') === 'Rascunho' ? 'checked' : '' }}>
                <label for="stDraft" class="af-status__opt af-status__opt--draft">
                  <i class="bi bi-pencil-square"></i> Rascunho
                </label>
              </div>
            </div>

            {{-- Date --}}
            <div>
              <label class="af-label">Data de publicação</label>
              <input type="datetime-local" name="published_at" class="form-control"
                     value="{{ old('published_at', isset($article->published_at) ? $article->published_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}">
            </div>

            {{-- Actions --}}
            <div class="d-flex flex-column gap-2 pt-1">
              <button type="submit" class="btn btn-primary-modern w-100">
                <i class="bi bi-check-lg me-1"></i>
                {{ $isEdit ? 'Guardar Alterações' : 'Criar Artigo' }}
              </button>
              @if($isEdit)
              <a href="{{ route('frontend.news-details', $article->slug) }}"
                 target="_blank" class="btn btn-secondary-modern w-100">
                <i class="bi bi-box-arrow-up-right me-1"></i> Ver no site
              </a>
              @endif
              <a href="{{ route('admin.news.index') }}"
                 class="btn btn-link text-muted small text-center w-100">
                Cancelar
              </a>
            </div>

          </div>
        </div>

        {{-- Imagem de Capa --}}
        <div class="modern-card">
          <div class="modern-card-header">
            <h5 class="modern-card-title"><i class="bi bi-image"></i> Imagem de Capa</h5>
          </div>

          {{-- Capa atual (edit com capa existente) --}}
          @if($isEdit && !empty($article->cover_image))
          <div id="coverCurrentWrap" class="mb-3">
            <label class="af-label">Capa atual</label>
            <div class="af-cover-wrap">
              <img src="{{ asset('storage/'.$article->cover_image) }}" alt="" class="af-cover-img">
              <button type="button" id="coverRemove" class="af-remove-btn" title="Remover capa atual">
                <i class="bi bi-x-lg"></i>
              </button>
            </div>
          </div>
          <input type="hidden" name="remove_cover" id="removeCoverFlag" value="0">
          @endif

          {{-- Preview da nova imagem selecionada --}}
          <div id="coverPreviewWrap" class="d-none mb-3">
            <label class="af-label">Nova capa</label>
            <div class="af-cover-wrap">
              <img id="coverImg" src="" alt="" class="af-cover-img">
              <button type="button" id="coverCancelNew" class="af-remove-btn" title="Cancelar">
                <i class="bi bi-x-lg"></i>
              </button>
            </div>
          </div>

          {{-- Drop zone (sempre visível) --}}
          <div id="coverDropZone" class="af-drop">
            <input type="file" name="cover_image" id="coverInput" accept="image/*" class="af-drop__input">
            <i class="bi bi-cloud-arrow-up af-drop__icon"></i>
            <p class="af-drop__text">
              {{ ($isEdit && !empty($article->cover_image)) ? 'Clica para substituir a capa' : 'Clica ou arrasta a imagem' }}
            </p>
            <p class="af-drop__hint">JPG · PNG · WebP · máx. 5 MB</p>
          </div>

          <p class="form-text mt-2 mb-0">Recomendado: 1200 × 630 px</p>
        </div>

        {{-- Galeria --}}
        <div class="modern-card">
          <div class="modern-card-header">
            <h5 class="modern-card-title"><i class="bi bi-images"></i> Galeria de Imagens</h5>
          </div>

          {{-- Existing images --}}
          <div class="af-gallery" id="galleryExisting">
            @if($isEdit && $article->gallery)
              @foreach($article->gallery as $gPath)
              <div class="af-gallery__item" data-path="{{ $gPath }}">
                <img src="{{ asset('storage/'.$gPath) }}" alt="">
                <button type="button" class="af-gallery__remove af-gallery-existing-remove"
                        data-article="{{ $article->id }}"
                        data-path="{{ $gPath }}" title="Remover">
                  <i class="bi bi-x-lg"></i>
                </button>
              </div>
              @endforeach
            @endif
          </div>

          {{-- New images preview --}}
          <div class="af-gallery mt-2" id="galleryNew"></div>

          {{-- Drop zone --}}
          <div class="af-drop mt-2" id="galleryDropZone">
            <input type="file" name="gallery[]" id="galleryInput"
                   accept="image/*" multiple class="af-drop__input">
            <i class="bi bi-plus-circle af-drop__icon"></i>
            <p class="af-drop__text">Adicionar à galeria</p>
            <p class="af-drop__hint">Podes seleccionar múltiplas imagens</p>
          </div>
        </div>

        {{-- SEO tips --}}
        <div class="modern-card" style="background:#f9fafb; border-color:#e5e7eb">
          <h6 class="small fw-bold text-muted text-uppercase mb-2" style="letter-spacing:.06em">
            Dicas de SEO
          </h6>
          <ul class="list-unstyled mb-0"
              style="font-size:.75rem; color:#6b7280; display:flex; flex-direction:column; gap:.4rem">
            <li><i class="bi bi-check2 text-success me-1"></i> Título com 50–60 caracteres</li>
            <li><i class="bi bi-check2 text-success me-1"></i> Meta descrição com 120–160 chars</li>
            <li><i class="bi bi-check2 text-success me-1"></i> Usa H2 e H3 para estruturar</li>
            <li><i class="bi bi-check2 text-success me-1"></i> Link interno para o simulador</li>
            <li><i class="bi bi-check2 text-success me-1"></i> Pelo menos 600 palavras</li>
          </ul>
        </div>

      </div>
    </div>

  </div>
</form>

</div>
@endsection

@push('scripts')
<script>
/* ─────────────────────────────────────────────
   CKEditor está no <head> (via @@push('styles'))
   DOM está pronto — estamos no fundo do <body>
───────────────────────────────────────────── */

var REMOVE = [
  /* colaboração em tempo real */
  'RealTimeCollaborativeComments','RealTimeCollaborativeEditing',
  'RealTimeCollaborativeTrackChanges','RealTimeCollaborativeRevisionHistory',
  'PresenceList',
  /* comentários / revisão */
  'Comments','CommentsOnly','TrackChanges','TrackChangesData',
  'RevisionHistory','AnnotationsUIs',
  /* premium sem licença */
  'Pagination','Template','SlashCommand','FormatPainter',
  'MultiLevelList','CaseChange',
  'TableOfContents','DocumentOutline',
  'ExportToPdf','ExportToWord','ImportWord',
  'PasteFromOfficeEnhanced',
  /* terceiros */
  'WProofreader'
];

function startCK(elId, toolbarItems, minHeight) {
  var el = document.getElementById(elId);
  if (!el) return;
  CKEDITOR.ClassicEditor.create(el, {
    toolbar: { items: toolbarItems, shouldNotGroupWhenFull: true },
    removePlugins: REMOVE,
  }).then(function(editor) {
    editor.ui.view.editable.element.style.minHeight = minHeight + 'px';
    if (elId === 'afContent') {
      function wc() {
        var words = editor.getData().replace(/<[^>]*>/g, '').trim().split(/\s+/).filter(Boolean).length;
        var mins  = Math.max(1, Math.round(words / 200));
        var el    = document.getElementById('afWc');
        if (el) el.textContent = words.toLocaleString('pt-PT') + ' palavras · ' + mins + ' min leitura';
      }
      editor.model.document.on('change:data', wc);
      wc();
    }
  }).catch(function(err) { console.error('[CKEditor ' + elId + ']', err); });
}

var SIMPLE = ['bold','italic','link','|','bulletedList','numberedList','|','undo','redo'];
var FULL   = ['heading','|','fontFamily','fontSize','|','bold','italic','underline','strikethrough','|',
              'fontColor','fontBackgroundColor','|',
              'link','|','bulletedList','numberedList','|','blockQuote','insertTable','|',
              'sourceEditing','|','undo','redo'];

startCK('afSubtitle', SIMPLE, 130);
startCK('afContent',  FULL,   420);
startCK('afSummary',  SIMPLE, 130);

/* ── Auto-slug ── */
var titleEl  = document.getElementById('afTitle');
var slugEl   = document.getElementById('afSlug');
var locked   = !!(slugEl && slugEl.value.length > 0);

function mkSlug(s) {
  return s.toLowerCase()
    .normalize('NFD').replace(/[̀-ͯ]/g, '')
    .replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-')
    .replace(/-+/g, '-').replace(/^-|-$/g, '');
}
if (titleEl && slugEl) {
  titleEl.addEventListener('input', function() { if (!locked) slugEl.value = mkSlug(this.value); });
  slugEl.addEventListener('input',  function() { locked = true; this.value = mkSlug(this.value); });
}

/* ── SEO counters ── */
function charCounter(fId, cId, max) {
  var f = document.getElementById(fId), c = document.getElementById(cId);
  if (!f || !c) return;
  function up() {
    var l = f.value.length;
    c.textContent = l + ' / ' + max;
    c.style.color = l > max ? '#dc2626' : l > max * .9 ? '#ca8a04' : '#9ca3af';
  }
  f.addEventListener('input', up); up();
}
charCounter('afSeoTitle', 'afSeoTitleC', 60);
charCounter('afSeoDesc',  'afSeoDescC',  160);

/* ── Cover image ── */
var coverInput       = document.getElementById('coverInput');
var coverDropZone    = document.getElementById('coverDropZone');
var coverPreview     = document.getElementById('coverPreviewWrap');
var coverImg         = document.getElementById('coverImg');
var coverRemove      = document.getElementById('coverRemove');
var coverCancelNew   = document.getElementById('coverCancelNew');
var coverCurrentWrap = document.getElementById('coverCurrentWrap');
var removeCoverFlag  = document.getElementById('removeCoverFlag');

function showCoverPreview(file) {
  var r = new FileReader();
  r.onload = function(e) {
    coverImg.src = e.target.result;
    coverPreview.classList.remove('d-none');
  };
  r.readAsDataURL(file);
}

if (coverInput) {
  coverInput.addEventListener('change', function() {
    if (this.files[0]) showCoverPreview(this.files[0]);
  });
}

/* X na capa atual → marca para remoção, esconde preview atual */
if (coverRemove) {
  coverRemove.addEventListener('click', function() {
    if (coverCurrentWrap) coverCurrentWrap.classList.add('d-none');
    if (removeCoverFlag)  removeCoverFlag.value = '1';
  });
}

/* X no preview da nova imagem → cancela o upload novo */
if (coverCancelNew) {
  coverCancelNew.addEventListener('click', function() {
    if (coverInput) coverInput.value = '';
    if (coverImg)   coverImg.src = '';
    coverPreview.classList.add('d-none');
  });
}

if (coverDropZone) {
  coverDropZone.addEventListener('dragover',  function(e) { e.preventDefault(); this.classList.add('af-drop--over'); });
  coverDropZone.addEventListener('dragleave', function()  { this.classList.remove('af-drop--over'); });
  coverDropZone.addEventListener('drop', function(e) {
    e.preventDefault(); this.classList.remove('af-drop--over');
    var file = e.dataTransfer.files[0];
    if (file && coverInput) {
      var dt = new DataTransfer(); dt.items.add(file); coverInput.files = dt.files;
      showCoverPreview(file);
    }
  });
}

/* ── Gallery — add new ── */
var galleryInput = document.getElementById('galleryInput');
var galleryNew   = document.getElementById('galleryNew');
var galleryDrop  = document.getElementById('galleryDropZone');

function addGalleryPreview(file) {
  var r = new FileReader();
  r.onload = function(e) {
    var div = document.createElement('div');
    div.className = 'af-gallery__item';
    div.innerHTML = '<img src="' + e.target.result + '" alt=""><button type="button" title="Remover"><i class="bi bi-x-lg"></i></button>';
    div.querySelector('button').addEventListener('click', function() { div.remove(); });
    galleryNew.appendChild(div);
  };
  r.readAsDataURL(file);
}
if (galleryInput) {
  galleryInput.addEventListener('change', function() {
    Array.from(this.files).forEach(addGalleryPreview);
  });
}
if (galleryDrop) {
  galleryDrop.addEventListener('dragover',  function(e) { e.preventDefault(); this.classList.add('af-drop--over'); });
  galleryDrop.addEventListener('dragleave', function()  { this.classList.remove('af-drop--over'); });
  galleryDrop.addEventListener('drop', function(e) {
    e.preventDefault(); this.classList.remove('af-drop--over');
    var files = Array.from(e.dataTransfer.files).filter(function(f) { return f.type.startsWith('image/'); });
    if (files.length && galleryInput) {
      var dt = new DataTransfer();
      files.forEach(function(f) { dt.items.add(f); addGalleryPreview(f); });
      galleryInput.files = dt.files;
    }
  });
}

/* ── Gallery — remove existing ── */
document.querySelectorAll('.af-gallery-existing-remove').forEach(function(btn) {
  btn.addEventListener('click', function() {
    var articleId = this.dataset.article;
    var path      = this.dataset.path;
    var item      = this.closest('.af-gallery__item');

    if (!articleId) { item.remove(); return; }

    fetch('{{ url("/gestao/news") }}/' + articleId + '/gallery-image', {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ path: path })
    }).then(function() { item.remove(); });
  });
});
</script>
@endpush
