@extends('layouts.admin')
@section('main-content')
<div class="container py-5">
    <h2 class="fw-bold mb-4 text-primary">Editar Página</h2>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('pages.update', $page) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="title" class="form-label">Título</label>
                <input type="text" name="title" id="title" class="form-control" required value="{{ old('title', $page->title) }}">
            </div>

            <div class="mb-3">
                <label for="slug" class="form-label">Slug</label>
                <input type="text" name="slug" id="slug" class="form-control" value="{{ old('slug', $page->slug) }}" required readonly>
            </div>

            <div class="col-md-4 mb-3">
                <label for="page_type_id" class="form-label">Tipo de Página</label>
                <select name="page_type_id_disabled" id="page_type_id" class="form-control" required disabled>
                    <option value="">-- Selecione --</option>
                    @foreach ($pageTypes as $type)
                    <option value="{{ $type->id }}" data-fields='@json($type->fields)' {{ old('page_type_id', $page->page_type_id) == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                    @endforeach
                </select>
                <input type="hidden" name="page_type_id" value="{{ old('page_type_id', $page->page_type_id) }}">
            </div>
        </div>

        <div id="dynamic-fields"></div>

        <div class="mt-4">
            <a href="{{ route('pages.index') }}" class="btn btn-danger">Cancelar</a>
            <button type="submit" class="btn btn-success">Salvar Alterações</button>
        </div>
    </form>
</div>

<!-- CKEditor 5 CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<script>
    const contents = @json($contents);
    const allPages = @json($allPages);

    function createInputField(field, value = null) {
        let inputHTML = '';
        const label = `<label class="form-label">${field.label}</label>`;

        switch (field.type) {
            case 'text':
                inputHTML = `<input type="text" name="fields[${field.label}]" class="form-control" ${field.is_required ? 'required' : ''} value="${value ? value : ''}">`;
                break;

            case 'textarea':
                inputHTML = `<textarea name="fields[${field.label}]" class="form-control ckeditor" ${field.is_required ? 'required' : ''}>${value ? value : ''}</textarea>`;
                break;

            case 'image':
                inputHTML = `
        <input type="file" name="fields[${field.label}]" accept="image/*" class="form-control" ${field.is_required ? 'required' : ''} onchange="previewImage(this)">
        <img src="${value ? value : ''}" alt="Preview" style="max-width: 200px; margin-top: 10px; ${value ? 'display:block' : 'display:none'}" class="preview-image">
    `;
                break;

            case 'gallery':
                inputHTML = `
        <input type="file" name="fields[${field.label}][]" accept="image/*" multiple class="form-control" ${field.is_required ? 'required' : ''} onchange="previewGallery(this)">
        <div class="gallery-preview d-flex flex-wrap gap-2 mt-2"></div>
        ${value ? renderGalleryPreview(JSON.parse(value)) : ''}
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
                const selectOptions = field.options ? field.options.split(',') : [];
                inputHTML = `
        <select name="fields[${field.label}]" class="form-control" ${field.is_required ? 'required' : ''}>
            ${selectOptions.map(o => `<option value="${o.trim()}" ${value == o.trim() ? 'selected' : ''}>${o.trim()}</option>`).join('')}
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
                <select name="fields[${field.label}][]" class="form-control" multiple>
                    ${allPages.map(page => `
                        <option value="${page.id}" ${(value && value.includes(page.id)) ? 'selected' : ''}>
                            ${page.title}
                        </option>
                    `).join('')}
                </select>
            `;
                break;

            default:
                inputHTML = `<input type="text" name="fields[${field.id}]" class="form-control" ${field.is_required ? 'required' : ''} value="${value ? value : ''}">`;
        }

        return `<div class="mb-3">${label}${inputHTML}</div>`;
    }

    function renderGalleryPreview(images) {
        if (!images || !Array.isArray(images)) return '';
        return images.map(src => `<img src="${src}" style="max-width: 100px; margin-right: 10px;">`).join('');
    }

    function previewImage(input) {
        const file = input.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            const img = input.nextElementSibling;
            img.src = e.target.result;
            img.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }

    function previewGallery(input) {
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

        // Inicializa CKEditor nos textareas
        document.querySelectorAll('.ckeditor').forEach(textarea => {
            ClassicEditor.create(textarea).catch(error => console.error(error));
        });
    });

    window.addEventListener('DOMContentLoaded', () => {
        const select = document.getElementById('page_type_id');
        if (select.value) {
            select.dispatchEvent(new Event('change'));
        }
    });
</script>
@endsection
