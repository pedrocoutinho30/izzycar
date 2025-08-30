@extends('layouts.admin')
@section('main-content')

<div class="container-fluid px-4 py-5">
    <h2 class="mb-4 fw-bold text-primary">
        Editar Tipo de Página
    </h2>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('page-types.update', $pageType->id) }}" method="POST" class="bg-white p-4 rounded shadow-sm">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Nome do Tipo de Página</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $pageType->name }}" required>
        </div>

        <hr>
        <h5>Campos</h5>
        <div id="fields-wrapper">
            @foreach ($pageType->fields as $index => $field)
            <div class="field-item mb-3 row">
                <input type="hidden" name="fields[{{ $index }}][id]" value="{{ $field->id }}">

                <div class="col-md-2">
                    <input type="text" name="fields[{{ $index }}][name]" class="form-control" placeholder="Nome do Campo" value="{{ $field->name }}" required>
                </div>
                <div class="col-md-2">
                    <input type="text" disabled name="fields[{{ $index }}][label]" class="form-control" placeholder="Label" value="{{ $field->label }}" required>
                    <input type="hidden" name="fields[{{ $index }}][label]" value="{{ $field->label }}" required>
                </div>
                <div class="col-md-2">
                    <select name="fields[{{ $index }}][type]" class="form-control field-type" required>
                        <option value="">Tipo de Campo</option>
                        @foreach (['text', 'textarea', 'image', 'gallery', 'boolean', 'select', 'radio', 'page'] as $type)
                        <option value="{{ $type }}" @selected($field->type === $type)> {{ ucfirst($type) }} </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <input type="number" name="fields[{{ $index }}][order]" class="form-control" placeholder="Ordem" value="{{ $field->order }}">
                </div>
                <div class="col-md-1">
                    <input type="checkbox" name="fields[{{ $index }}][is_required]" value="1" @checked($field->is_required)>
                    <label>Obrig?</label>
                </div>
                <div class="col-md-3">
                    <input type="text" name="fields[{{ $index }}][options]" class="form-control options-field" placeholder="Opções (se aplicável)" value="{{ $field->options }}">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger remove-field w-100">Remover</button>
                </div>
            </div>
            @endforeach
        </div>

        <button type="button" id="add-field" class="btn btn-secondary mb-3">Adicionar Campo</button>

        <div class="d-flex justify-content-end">
            <a href="{{ route('page-types.index') }}" class="btn btn-danger px-4 py-2 rounded-pill shadow">Voltar</a>
            <button type="submit" class="btn btn-success px-4 py-2 rounded-pill shadow">
                <i class="bi bi-check-circle me-1"></i> Salvar
            </button>
        </div>
    </form>
</div>

<script>
    let fieldIndex = {{$pageType->fields->count()}};

    document.getElementById('add-field').addEventListener('click', function() {
        const wrapper = document.getElementById('fields-wrapper');

        const fieldHTML = `
            <div class="field-item mb-3 row">
                <div class="col-md-2">
                    <input type="text" name="fields[${fieldIndex}][name]" class="form-control" placeholder="Nome do Campo" required>
                </div>
                <div class="col-md-2">
                    <input type="text" name="fields[${fieldIndex}][label]" class="form-control" placeholder="Label" required>
                </div>
                <div class="col-md-2">
                    <select name="fields[${fieldIndex}][type]" class="form-control field-type" required>
                        <option value="">Tipo de Campo</option>
                        <option value="text">Texto</option>
                        <option value="textarea">Área de Texto</option>
                        <option value="image">Imagem</option>
                        <option value="gallery">Galeria</option>
                        <option value="boolean">Boolean</option>
                        <option value="select">Select</option>
                        <option value="radio">Radio</option>
                        <option value="page">Páginas</option>
                        <option value="date">Data</option>
                        <option value="datetime">Data e Hora</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <input type="number" name="fields[${fieldIndex}][order]" class="form-control" placeholder="Ordem">
                </div>
                <div class="col-md-1">
                    <input type="checkbox" name="fields[${fieldIndex}][is_required]" value="1">
                    <label>Obrig?</label>
                </div>
                <div class="col-md-3">
                    <input type="text" name="fields[${fieldIndex}][options]" class="form-control options-field" placeholder="Opções">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger remove-field w-100">Remover</button>
                </div>
            </div>
        `;
        wrapper.insertAdjacentHTML('beforeend', fieldHTML);
        fieldIndex++;
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-field')) {
            e.target.closest('.field-item').remove();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        function toggleOptionsVisibility() {
            document.querySelectorAll('.field-item').forEach((item) => {
                const type = item.querySelector('.field-type');
                const options = item.querySelector('.options-field');

                if (type && options) {
                    if (type.value === 'select' || type.value === 'radio') {
                        options.classList.remove('d-none');
                    } else {
                        options.classList.add('d-none');
                        options.value = '';
                    }
                }
            });
        }

        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('field-type')) {
                toggleOptionsVisibility();
            }
        });

        toggleOptionsVisibility(); // inicializar
    });
</script>
@endsection