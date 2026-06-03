@extends('layouts.admin-v2')
@section('title', isset($page) ? 'Editar Artigo' : 'Novo Artigo')

{{-- CKEditor no <head> via @push('styles') — garante que está disponível antes do @stack('scripts') --}}
@push('styles')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/super-build/ckeditor.js"></script>
<style>
/* ── form styles ── */
.nf-label{display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#374151;margin-bottom:.4rem}
.nf-req{color:#6e0707}
.nf-hint{font-size:.72rem;color:#9ca3af;margin-top:.2rem}
.nf-char{font-size:.72rem;text-align:right;margin-top:.2rem;color:#9ca3af}

.nf-status{display:grid;grid-template-columns:1fr 1fr;gap:.5rem}
.nf-status input[type=radio]{display:none}
.nf-status__lbl{display:flex;align-items:center;justify-content:center;gap:.4rem;padding:.6rem;border-radius:10px;border:2px solid #e5e7eb;cursor:pointer;font-size:.82rem;font-weight:600;color:#6b7280;transition:.15s}
.nf-status input:checked+.nf-status__lbl--pub{border-color:#16a34a;background:#f0fdf4;color:#16a34a}
.nf-status input:checked+.nf-status__lbl--draft{border-color:#ca8a04;background:#fefce8;color:#ca8a04}

.nf-drop{border:2px dashed #e5e7eb;border-radius:12px;padding:1.75rem;text-align:center;cursor:pointer;position:relative;transition:.2s}
.nf-drop:hover,.nf-drop.over{border-color:#6e0707;background:#fdf2f8}
.nf-drop input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.nf-cover-preview{position:relative;border-radius:10px;overflow:hidden;border:1px solid #e5e7eb}
.nf-cover-preview img{width:100%;display:block;max-height:180px;object-fit:cover}
.nf-cover-remove{position:absolute;top:.4rem;right:.4rem;width:26px;height:26px;border-radius:7px;background:rgba(0,0,0,.65);color:#fff;border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.7rem;transition:.2s}
.nf-cover-remove:hover{background:rgba(220,38,38,.9)}

.nf-gallery-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:.5rem;margin-top:.75rem}
.nf-gallery-item{position:relative;aspect-ratio:1;border-radius:8px;overflow:hidden;border:1px solid #e5e7eb}
.nf-gallery-item img{width:100%;height:100%;object-fit:cover;display:block}
.nf-gallery-item button{position:absolute;top:.2rem;right:.2rem;width:22px;height:22px;border-radius:6px;background:rgba(0,0,0,.65);color:#fff;border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.65rem;transition:.2s}
.nf-gallery-item button:hover{background:rgba(220,38,38,.9)}

.nf-wc{font-size:.72rem;color:#9ca3af;font-weight:500}
.ck.ck-editor__editable_type_inline{min-height:120px!important}
</style>
@endpush

@section('content')
<div class="admin-content">

@php $isEdit = isset($page); $c = $contents ?? collect(); @endphp

@include('components.admin.page-header', [
  'breadcrumbs' => [
    ['icon'=>'bi bi-house-door','label'=>'Dashboard','href'=>route('admin.v2.dashboard')],
    ['icon'=>'bi bi-newspaper','label'=>'Notícias','href'=>route('admin.v3.news.index')],
    ['icon'=>'','label'=> $isEdit ? 'Editar' : 'Novo Artigo'],
  ],
  'title'       => $isEdit ? 'Editar Artigo' : 'Novo Artigo',
  'subtitle'    => '',
  'actionHref'  => '', 'actionLabel' => '',
])

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4">
  <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
  <button class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ $isEdit ? route('admin.v3.news.update',$page->id) : route('admin.v3.news.store') }}"
      method="POST" enctype="multipart/form-data" id="nfForm">
  @csrf
  @if($isEdit) @method('PUT') @endif

  <div class="row g-4">

    {{-- ═══════════════ LEFT ═══════════════ --}}
    <div class="col-lg-8">

      {{-- Título + Slug --}}
      <div class="modern-card mb-4">
        <div class="modern-card-header">
          <h5 class="modern-card-title"><i class="bi bi-type-h1"></i> Título & URL</h5>
        </div>
        <div class="row g-3">
          <div class="col-12">
            <label class="nf-label">Título <span class="nf-req">*</span></label>
            <input type="text" name="title" id="nfTitle"
                   class="form-control form-control-lg fw-semibold @error('title') is-invalid @enderror"
                   value="{{ old('title', $c['title'] ?? '') }}" placeholder="Título do artigo" required>
            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-12">
            <label class="nf-label">URL</label>
            <div class="input-group">
              <span class="input-group-text text-muted small">/noticias/</span>
              <input type="text" name="slug" id="nfSlug"
                     class="form-control @error('slug') is-invalid @enderror"
                     value="{{ old('slug', $page->slug ?? '') }}" placeholder="url-do-artigo" required>
            </div>
            @error('slug')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
          </div>
        </div>
      </div>

      {{-- Subtítulo --}}
      <div class="modern-card mb-4">
        <div class="modern-card-header">
          <h5 class="modern-card-title"><i class="bi bi-blockquote-left"></i> Subtítulo / Introdução</h5>
          <span class="badge bg-light border text-muted small">Destaque no início do artigo</span>
        </div>
        <textarea id="nfSubtitle" name="subtitle">{{ old('subtitle', $c['subtitle'] ?? '') }}</textarea>
      </div>

      {{-- Conteúdo --}}
      <div class="modern-card mb-4">
        <div class="modern-card-header">
          <h5 class="modern-card-title"><i class="bi bi-file-text"></i> Conteúdo</h5>
          <span class="nf-wc" id="nfWc">—</span>
        </div>
        <textarea id="nfContent" name="content"
                  class="@error('content') is-invalid @enderror">{{ old('content', $c['content'] ?? '') }}</textarea>
        @error('content')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
      </div>

      {{-- Sumário --}}
      <div class="modern-card mb-4">
        <div class="modern-card-header">
          <h5 class="modern-card-title"><i class="bi bi-check2-circle"></i> Sumário / Conclusão</h5>
          <span class="badge bg-light border text-muted small">Caixa de destaque no final</span>
        </div>
        <textarea id="nfSummary" name="summary">{{ old('summary', $c['summary'] ?? '') }}</textarea>
      </div>

      {{-- SEO --}}
      <div class="modern-card mb-4">
        <div class="modern-card-header" style="cursor:pointer" data-bs-toggle="collapse" data-bs-target="#seoBox">
          <h5 class="modern-card-title"><i class="bi bi-search"></i> SEO</h5>
          <i class="bi bi-chevron-down ms-auto text-muted"></i>
        </div>
        <div class="collapse" id="seoBox">
          <div class="row g-3 pt-2">
            <div class="col-12">
              <label class="nf-label">Título SEO</label>
              <input type="text" name="seo_title" id="nfSeoTitle" maxlength="80"
                     class="form-control" placeholder="Título para o Google (recomendado: até 60 chars)"
                     value="{{ old('seo_title', $page->seo->title ?? $c['title'] ?? '') }}">
              <div class="nf-char" id="nfSeoTitleC">0 / 60</div>
            </div>
            <div class="col-12">
              <label class="nf-label">Meta Descrição</label>
              <textarea name="seo_description" id="nfSeoDesc" maxlength="200" rows="2"
                        class="form-control" placeholder="Descrição para o Google (recomendado: 120–160 chars)">{{ old('seo_description', $page->seo->meta_description ?? '') }}</textarea>
              <div class="nf-char" id="nfSeoDescC">0 / 160</div>
            </div>
            <div class="col-12">
              <label class="nf-label">Palavras-chave</label>
              <input type="text" name="seo_keywords" class="form-control"
                     placeholder="isv, importação automóvel, ..."
                     value="{{ old('seo_keywords', $page->seo->meta_keywords ?? '') }}">
            </div>
          </div>
        </div>
      </div>

    </div>

    {{-- ═══════════════ RIGHT ═══════════════ --}}
    <div class="col-lg-4">
      <div style="position:sticky;top:100px;display:flex;flex-direction:column;gap:1.25rem">

        {{-- Publicação --}}
        <div class="modern-card">
          <div class="modern-card-header">
            <h5 class="modern-card-title"><i class="bi bi-send"></i> Publicação</h5>
          </div>
          <div class="d-flex flex-column gap-3">
            <div>
              <label class="nf-label">Estado <span class="nf-req">*</span></label>
              <div class="nf-status">
                <input type="radio" name="status" value="Publicado" id="stPub"
                       {{ old('status',$c['status']??'Rascunho')==='Publicado'?'checked':'' }}>
                <label for="stPub" class="nf-status__lbl nf-status__lbl--pub">
                  <i class="bi bi-check-circle-fill"></i> Publicado
                </label>
                <input type="radio" name="status" value="Rascunho" id="stDraft"
                       {{ old('status',$c['status']??'Rascunho')==='Rascunho'?'checked':'' }}>
                <label for="stDraft" class="nf-status__lbl nf-status__lbl--draft">
                  <i class="bi bi-pencil-square"></i> Rascunho
                </label>
              </div>
            </div>
            <div>
              <label class="nf-label">Data de publicação <span class="nf-req">*</span></label>
              <input type="datetime-local" name="date" class="form-control"
                     value="{{ old('date', isset($c['date']) ? \Carbon\Carbon::parse($c['date'])->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}">
            </div>
            <div class="d-flex flex-column gap-2">
              <button type="submit" class="btn btn-primary-modern w-100">
                <i class="bi bi-check-lg me-1"></i>
                {{ $isEdit ? 'Guardar Alterações' : 'Criar Artigo' }}
              </button>
              @if($isEdit)
              <a href="{{ route('frontend.news-details',$page->slug) }}" target="_blank"
                 class="btn btn-secondary-modern w-100">
                <i class="bi bi-box-arrow-up-right me-1"></i> Ver no site
              </a>
              @endif
              <a href="{{ route('admin.v3.news.index') }}" class="btn btn-link text-muted text-center small w-100">
                Cancelar
              </a>
            </div>
          </div>
        </div>

        {{-- Imagem de capa --}}
        <div class="modern-card">
          <div class="modern-card-header">
            <h5 class="modern-card-title"><i class="bi bi-image"></i> Imagem de Capa</h5>
          </div>
          <div id="coverPreviewWrap" class="{{ isset($c['image']) && $c['image'] ? '' : 'd-none' }} mb-3">
            <div class="nf-cover-preview">
              <img id="coverPreviewImg"
                   src="{{ isset($c['image']) && $c['image'] ? asset('storage/'.$c['image']) : '' }}" alt="">
              <button type="button" id="coverRemove" class="nf-cover-remove" title="Remover">
                <i class="bi bi-x-lg"></i>
              </button>
            </div>
          </div>
          <div id="coverDrop" class="nf-drop {{ isset($c['image']) && $c['image'] ? 'd-none' : '' }}">
            <input type="file" name="image" id="coverInput" accept="image/*">
            <i class="bi bi-cloud-arrow-up fs-3 text-muted d-block mb-1"></i>
            <p class="small fw-semibold mb-0">Clica ou arrasta</p>
            <p class="text-muted mb-0" style="font-size:.7rem">JPG · PNG · WebP · max 5MB</p>
          </div>
          <p class="text-muted mt-2 mb-0" style="font-size:.72rem">Recomendado: 1200×630px</p>
        </div>

        {{-- Galeria --}}
        <div class="modern-card">
          <div class="modern-card-header">
            <h5 class="modern-card-title"><i class="bi bi-images"></i> Galeria</h5>
          </div>

          {{-- Existing gallery images --}}
          @php $gallery = json_decode($c['gallery'] ?? '[]', true) ?? []; @endphp
          <div class="nf-gallery-grid" id="galleryGrid">
            @foreach($gallery as $gimg)
            <div class="nf-gallery-item" data-path="{{ $gimg }}">
              <img src="{{ asset('storage/'.$gimg) }}" alt="">
              <button type="button" class="gal-remove-existing"
                      data-page="{{ $isEdit ? $page->id : '' }}"
                      data-path="{{ $gimg }}" title="Remover">
                <i class="bi bi-x-lg"></i>
              </button>
            </div>
            @endforeach
          </div>

          <div class="nf-drop mt-3" id="galleryDrop">
            <input type="file" name="gallery[]" id="galleryInput" accept="image/*" multiple>
            <i class="bi bi-plus-circle fs-3 text-muted d-block mb-1"></i>
            <p class="small fw-semibold mb-0">Adicionar imagens</p>
            <p class="text-muted mb-0" style="font-size:.7rem">Múltiplas imagens permitidas</p>
          </div>

          {{-- New gallery previews (before submit) --}}
          <div class="nf-gallery-grid mt-2" id="galleryNewGrid"></div>
        </div>

        {{-- Tips --}}
        <div class="modern-card" style="background:#f9fafb;border-color:#e5e7eb">
          <h6 class="small fw-bold text-muted text-uppercase mb-2" style="letter-spacing:.06em">Dicas SEO</h6>
          <ul class="list-unstyled mb-0" style="font-size:.75rem;color:#6b7280;display:flex;flex-direction:column;gap:.4rem">
            <li><i class="bi bi-check2 text-success me-1"></i>Título: 50–60 caracteres</li>
            <li><i class="bi bi-check2 text-success me-1"></i>Meta descrição: 120–160 chars</li>
            <li><i class="bi bi-check2 text-success me-1"></i>Usa H2/H3 para estruturar</li>
            <li><i class="bi bi-check2 text-success me-1"></i>Link para o simulador</li>
            <li><i class="bi bi-check2 text-success me-1"></i>600+ palavras</li>
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
/* ═══════════════════════════════════════════
   Toda a init corre aqui, no fundo do <body>.
   O CKEditor já está carregado (veio do <head>
   via @push('styles')).
═══════════════════════════════════════════ */

var REMOVE_PLUGINS = [
  'RealTimeCollaborativeComments','PresenceList','RealTimeCollaborativeTrackChanges',
  'RealTimeCollaborativeRevisionHistory','AnnotationsUIs','RevisionHistory',
  'TrackChanges','TrackChangesData','CommentsOnly','MultiLevelList','FormatPainter',
  'TableOfContents','PasteFromOfficeEnhanced','SlashCommand','AIAssistant','CaseChange','WProofreader'
];

/* ── CKEditor ── */
function initCK(id, toolbar, minH) {
  var el = document.getElementById(id);
  if (!el) return Promise.resolve(null);
  return CKEDITOR.ClassicEditor.create(el, {
    toolbar: toolbar,
    removePlugins: REMOVE_PLUGINS,
  }).then(function(ed) {
    ed.ui.view.editable.element.style.minHeight = (minH || 140) + 'px';
    return ed;
  }).catch(function(e) { console.error('[CKEditor]', id, e); return null; });
}

var TOOLBAR_SIMPLE = {items:['bold','italic','link','|','bulletedList','numberedList','|','undo','redo']};
var TOOLBAR_FULL   = {items:['heading','|','bold','italic','underline','|','fontColor','|','link','|','bulletedList','numberedList','|','blockQuote','insertTable','|','sourceEditing','|','undo','redo'],shouldNotGroupWhenFull:true};

initCK('nfSubtitle', TOOLBAR_SIMPLE, 120);
initCK('nfSummary',  TOOLBAR_SIMPLE, 120);
initCK('nfContent',  TOOLBAR_FULL,   380).then(function(ed) {
  if (!ed) return;
  function wc() {
    var t = ed.getData().replace(/<[^>]*>/g,'');
    var w = t.trim() ? t.trim().split(/\s+/).length : 0;
    var m = Math.max(1, Math.round(w/200));
    var el = document.getElementById('nfWc');
    if (el) el.textContent = w.toLocaleString('pt-PT') + ' palavras · ' + m + ' min';
  }
  ed.model.document.on('change:data', wc);
  wc();
});

/* ── Auto-slug ── */
var titleEl  = document.getElementById('nfTitle');
var slugEl   = document.getElementById('nfSlug');
var slugLock = !!(slugEl && slugEl.value.length > 0);

function mkSlug(s) {
  return s.toLowerCase()
    .normalize('NFD').replace(/[̀-ͯ]/g,'')
    .replace(/[^a-z0-9\s-]/g,'').replace(/\s+/g,'-')
    .replace(/-+/g,'-').replace(/^-|-$/g,'');
}
if (titleEl && slugEl) {
  titleEl.addEventListener('input', function(){ if(!slugLock) slugEl.value = mkSlug(this.value); });
  slugEl.addEventListener('input',  function(){ slugLock = true; this.value = mkSlug(this.value); });
}

/* ── SEO char counters ── */
function charCount(fId, cId, max) {
  var f = document.getElementById(fId), c = document.getElementById(cId);
  if (!f || !c) return;
  function u(){ var l=f.value.length; c.textContent=l+' / '+max; c.style.color=l>max?'#dc2626':l>max*.9?'#ca8a04':'#9ca3af'; }
  f.addEventListener('input', u); u();
}
charCount('nfSeoTitle', 'nfSeoTitleC', 60);
charCount('nfSeoDesc',  'nfSeoDescC',  160);

/* ── Cover image ── */
var coverInput   = document.getElementById('coverInput');
var coverDrop    = document.getElementById('coverDrop');
var coverPreview = document.getElementById('coverPreviewWrap');
var coverImg     = document.getElementById('coverPreviewImg');
var coverRemove  = document.getElementById('coverRemove');

function showCover(file) {
  if (!file) return;
  var r = new FileReader();
  r.onload = function(e) {
    coverImg.src = e.target.result;
    coverPreview.classList.remove('d-none');
    coverDrop.classList.add('d-none');
  };
  r.readAsDataURL(file);
}
if (coverInput)  coverInput.addEventListener('change',  function(){ showCover(this.files[0]); });
if (coverRemove) coverRemove.addEventListener('click', function(){
  coverInput.value = ''; coverImg.src = '';
  coverPreview.classList.add('d-none'); coverDrop.classList.remove('d-none');
});
if (coverDrop) {
  coverDrop.addEventListener('dragover',  function(e){ e.preventDefault(); this.classList.add('over'); });
  coverDrop.addEventListener('dragleave', function(){ this.classList.remove('over'); });
  coverDrop.addEventListener('drop', function(e){
    e.preventDefault(); this.classList.remove('over');
    var f = e.dataTransfer.files[0];
    if (f) { showCover(f); var dt=new DataTransfer(); dt.items.add(f); coverInput.files=dt.files; }
  });
}

/* ── Gallery new previews ── */
var galleryInput   = document.getElementById('galleryInput');
var galleryNewGrid = document.getElementById('galleryNewGrid');

if (galleryInput) {
  galleryInput.addEventListener('change', function(){
    Array.from(this.files).forEach(function(file){
      var r = new FileReader();
      r.onload = function(e){
        var div = document.createElement('div');
        div.className = 'nf-gallery-item';
        div.innerHTML = '<img src="'+e.target.result+'" alt=""><button type="button" title="Remover"><i class="bi bi-x-lg"></i></button>';
        div.querySelector('button').addEventListener('click', function(){ div.remove(); });
        galleryNewGrid.appendChild(div);
      };
      r.readAsDataURL(file);
    });
  });
}

/* ── Remove existing gallery image (AJAX) ── */
document.querySelectorAll('.gal-remove-existing').forEach(function(btn){
  btn.addEventListener('click', function(){
    var pageId = this.dataset.page;
    var path   = this.dataset.path;
    var item   = this.closest('.nf-gallery-item');
    if (!pageId) { item.remove(); return; }
    fetch('/gestao/v2/news/'+pageId+'/gallery-image', {
      method:'DELETE',
      headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'},
      body: JSON.stringify({path: path})
    }).then(function(){ item.remove(); });
  });
});
</script>
@endpush
