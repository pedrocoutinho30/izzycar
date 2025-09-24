@extends('layouts.admin')
@section('main-content')
<div class="container py-5">
    <h2 class="fw-bold mb-4 text-primary">
        {{ isset($page) ? 'Editar Página' : 'Criar Página' }}
    </h2>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form
        action="{{ isset($page) ? route('pages.update', $page) : route('pages.store') }}"
        method="POST"
        enctype="multipart/form-data">
        @csrf
        @if(isset($page))
        @method('PUT')
        @endif

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="title" class="form-label">Título</label>
                <input
                    type="text"
                    name="title"
                    id="title"
                    class="form-control"
                    required
                    value="{{ old('title', $page->title ?? '') }}">
            </div>

            <div class="mb-3">
                <label for="slug" class="form-label">Slug</label>
                <input type="text" name="slug" id="slug"
                    class="form-control"
                    value="{{ old('slug', $page->slug ?? '') }}"
                    readonly required>
            </div>

            <div class="col-md-4 mb-3">
                <label for="page_type_id" class="form-label">Tipo de Página</label>
                <select
                    name="page_type_id{{ isset($page) ? '_disabled' : '' }}"
                    id="page_type_id"
                    class="form-control"
                    required
                    {{ isset($page) ? 'disabled' : '' }}>
                    <option value="">-- Selecione --</option>
                    @foreach ($pageTypes as $type)
                    <option value="{{ $type->id }}"
                        data-fields='@json($type->fields)'
                        {{ old('page_type_id', $page_type_id ?? '') == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                    @endforeach
                </select>
                @if(isset($page))
                <input type="hidden" name="page_type_id" value="{{ old('page_type_id', $page->page_type_id ?? '') }}">
                @endif
            </div>
        </div>

        <div id="dynamic-fields"></div>

        @include('partials.seo_form', ['model' => $page ?? null])
        <div class="mt-4">
            <a href="{{ route('pages.index') }}" class="btn btn-danger">Cancelar</a>
            <button type="submit" class="btn btn-success">
                {{ isset($page) ? 'Salvar Alterações' : 'Criar Página' }}
            </button>
        </div>
    </form>
</div>

<!-- CKEditor 5 -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/super-build/ckeditor.js"></script>

<!-- Tom Select -->
<link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
<!-- Template invisível do image manager para clonar -->
<div id="image-manager-template" style="display:none;">
    <x-image-manager images="IMAGE_MANAGER_OLD_IMAGES" multi="false" label="IMAGE_MANAGER_TITLE" id="IMAGE_MANAGER_PLACEHOLDER" name="fields[IMAGE_MANAGER_PLACEHOLDER]" />
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        function initTomSelect(container = document) {
            container.querySelectorAll('.tom-select').forEach(el => {
                if (!el.tomselect) {
                    new TomSelect(el, {
                        plugins: ['remove_button'],
                        placeholder: 'Seleciona as páginas...'
                    });
                }
            });
        }

        const titleInput = document.getElementById("title");
        const slugInput = document.getElementById("slug");

        if (titleInput && slugInput && !slugInput.value) {
            titleInput.addEventListener("input", function() {
                let slug = titleInput.value
                    .normalize("NFD") // separa letras e acentos
                    .replace(/[\u0300-\u036f]/g, "") // remove acentos
                    .replace(/ç/g, "c") // substitui cedilha
                    .replace(/[^a-z0-9]+/gi, '-') // troca não alfanuméricos por -
                    .replace(/^-+|-+$/g, '') // remove traços no início/fim
                    .toLowerCase();

                slugInput.value = slug;
            });
        }

        const contents = @json($contents ?? []);
        const allPages = @json($allPages);
        const storageBaseUrl = "{{ asset('storage') }}/";
        let imageManagerCounter = 0;

        function createInputField(field, value = null) {

            let inputHTML = '';
            const label = `<label class="form-label">${field.name}</label>`;

            switch (field.type) {
                case 'text':
                    inputHTML = `<input type="text" name="fields[${field.label}]" class="form-control" ${field.is_required ? 'required' : ''} value="${value ?? ''}">`;
                    break;

                case 'textarea':
                    inputHTML = `<textarea name="fields[${field.label}]" class="form-control ckeditor" ${field.is_required ? 'required' : ''}>${value ?? ''}</textarea>`;
                    break;

                case 'image':
                    console.log("field=> ", value);

                    const imgId = field.label;
                    let template = document.getElementById('image-manager-template').innerHTML;
                    template = template.replace(/IMAGE_MANAGER_PLACEHOLDER/g, imgId).replace(/IMAGE_MANAGER_TITLE/g, label);


                    let imagesValue = [];
                    if (Array.isArray(value)) {
                        imagesValue = value; // já é array
                    } else if (value) {
                        imagesValue = value.split(',').map(v => v.trim()); // transforma string em array
                    }
                    template = template.replace(/IMAGE_MANAGER_OLD_IMAGES/g, imagesValue);


                    return `<div class="mb-3">${template}</div>`;

                    break;

                case 'gallery':
                    inputHTML = `
        <div class="gallery-wrapper">
            <div class="d-flex align-items-center gap-2">
                <input type="file" name="fields[${field.label}][]" 
                       accept="image/*" multiple 
                       class="form-control" 
                       ${field.is_required ? 'required' : ''} 
                       onchange="previewGallery(this, '${field.label}')">
                <button type="button" class="btn btn-sm btn-outline-danger" 
                        onclick="clearGallery(this)">
                    Limpar imagens
                </button>
            </div>
            <div class="gallery-preview d-flex flex-wrap gap-2 mt-2">
                ${value ? renderGalleryPreview(JSON.parse(value), field.label) : ''}
            </div>
        </div>
    `;
                    break;

                case 'boolean':
                    inputHTML = `
                        <select name="fields[${field.label}]" class="form-select" ${field.is_required ? 'required' : ''}>
                            <option value="1" ${value == '1' ? 'selected' : ''}>Sim</option>
                            <option value="0" ${value == '0' ? 'selected' : ''}>Não</option>
                        </select>`;
                    break;

                case 'select':
                    let selectOptions = [];
                    try {
                        selectOptions = field.options ? JSON.parse(field.options) : [];
                    } catch (e) {
                        console.error("Erro ao parsear opções:", e);
                    }
                    inputHTML = `
                        <select name="fields[${field.label}]" class="form-control" ${field.is_required ? 'required' : ''}>
                            ${selectOptions.map(o => `<option value="${o}" ${value == o ? 'selected' : ''}>${o}</option>`).join('')}
                        </select>`;
                    break;

                case 'radio':
                    const radioOptions = field.options ? field.options.split(',') : [];
                    inputHTML = radioOptions.map(o => `
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="fields[${field.label}]" value="${o.trim()}" ${value == o.trim() ? 'checked' : ''} ${field.is_required ? 'required' : ''}>
                            <label class="form-check-label">${o.trim()}</label>
                        </div>
                    `).join('');
                    break;

                case 'page':
                    inputHTML = `
                        <select name="fields[${field.label}][]" class=" tom-select" multiple>
                            ${allPages
                                .filter(page => page.page_type_id == field.page_type)
                                .map(page => `
                                   <option value="${page.id}" ${Array.isArray(value) && value.includes(page.id) ? 'selected' : ''}>
                                        ${page.title}
                                    </option>
                                `).join('')}
                        </select>
                    `;
                    break;

                case 'date':
                    inputHTML = `<input type="date" name="fields[${field.label}]" class="form-control" ${field.is_required ? 'required' : ''} value="${value ?? ''}">`;
                    break;

                case 'datetime':
                    inputHTML = `<input type="datetime-local" name="fields[${field.label}]" class="form-control" ${field.is_required ? 'required' : ''} value="${value ? value.replace(' ', 'T') : ''}">`;
                    break;

                default:
                    inputHTML = `<input type="text" name="fields[${field.label}]" class="form-control" ${field.is_required ? 'required' : ''} value="${value ?? ''}">`;
            }

            return `<div class="mb-3">${label}${inputHTML}</div>`;
        }

        function renderGalleryPreview(images) {
            if (!images || !Array.isArray(images)) return '';
            return images.map(value => {
                const imageUrl = value ? storageBaseUrl + value : '';
                return `<img src="${imageUrl}" style="max-width: 100px; margin-right: 10px;">`;
            }).join('');
        }

        window.previewImage = function(input) {
            const file = input.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = input.nextElementSibling.nextElementSibling;
                img.src = e.target.result;
                img.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }

        window.previewGallery = function(input) {
            const previewWrapper = input.nextElementSibling;
            previewWrapper.innerHTML = '';
            Array.from(input.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.maxWidth = '100px';
                    img.style.marginRight = '10px';
                    previewWrapper.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        }

        document.getElementById('page_type_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const fields = JSON.parse(selectedOption.getAttribute('data-fields'));
            const wrapper = document.getElementById('dynamic-fields');

            wrapper.innerHTML = '';

            fields.sort((a, b) => a.order - b.order).forEach(field => {
                const value = contents[field.label] ?? null;
                wrapper.insertAdjacentHTML('beforeend', createInputField(field, value));
            });

            // CKEditor
            document.querySelectorAll('.ckeditor').forEach(textarea => {
                CKEDITOR.ClassicEditor.create(textarea, {
                    toolbar: [
                        'heading', '|',
                        'bold', 'italic', 'underline', 'strikethrough', 'link', '|',
                        'bulletedList', 'numberedList', 'outdent', 'indent', '|',
                        'alignment', '|',
                        'blockQuote', 'insertTable', 'mediaEmbed', 'imageUpload', '|',
                        'fontColor', 'fontBackgroundColor', 'fontSize', 'fontFamily', '|',
                        'undo', 'redo', '|',
                        'sourceEditing'
                    ],
                    fontColor: {
                        colors: [{
                                color: '#6e0707', // tua cor personalizada
                                label: 'Accent'
                            }, {
                                color: '#111111', // tua cor personalizada
                                label: 'Preto'
                            }, {
                                color: '#514f4f', // tua cor personalizada
                                label: 'Muted'
                            },
                            {
                                color: '#cccccc', // tua cor personalizada
                                label: 'P-color'
                            },
                            {
                                color: '#986b33', // tua cor personalizada
                                label: 'yellow'
                            }

                        ],
                        columns: 5
                    },
                    fontBackgroundColor: {
                        colors: [{
                                color: '#6e0707', // tua cor personalizada
                                label: 'Accent'
                            }, {
                                color: '#111111', // tua cor personalizada
                                label: 'Preto'
                            }, {
                                color: '#514f4f', // tua cor personalizada
                                label: 'Muted'
                            },
                            {
                                color: '#cccccc', // tua cor personalizada
                                label: 'P-color'
                            },
                            {
                                color: '#986b33', // tua cor personalizada
                                label: 'yellow'
                            }
                        ],
                        columns: 5
                    },

                    placeholder: 'Escreve aqui o conteúdo (ou edita em HTML)...',

                    // 🔴 Desativa plugins de colaboração e premium
                    removePlugins: [
                        'RealTimeCollaborativeComments',
                        'RealTimeCollaborativeTrackChanges',
                        'RealTimeCollaborativeRevisionHistory',
                        'PresenceList',
                        'Comments',
                        'TrackChanges',
                        'TrackChangesData',
                        'RevisionHistory',
                        'Pagination',
                        'WProofreader',
                        'MathType',
                        'SlashCommand',
                        'Template',
                        'DocumentOutline',
                        'FormatPainter',
                        'TableOfContents',
                        'PasteFromOfficeEnhanced',
                        'CaseChange'
                    ]
                }).catch(error => console.error(error));
            });

            // Tom Select
            initTomSelect(wrapper);
        });

        // Inicializa se já houver tipo de página selecionado
        const select = document.getElementById('page_type_id');
        if (select.value) {
            select.dispatchEvent(new Event('change'));
        }

        // Inicializa selects estáticos
        initTomSelect(document);
    });

    function clearGallery(button) {
        const wrapper = button.closest('.gallery-wrapper');
        if (!wrapper) return;

        // Limpa o input file
        const input = wrapper.querySelector('input[type="file"]');
        if (input) input.value = "";

        // Limpa o preview (imagens + hidden inputs)
        const preview = wrapper.querySelector('.gallery-preview');
        if (preview) preview.innerHTML = "";
    }
</script>
@endsection