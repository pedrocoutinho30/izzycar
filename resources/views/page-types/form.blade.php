@extends('layouts.admin')
@section('main-content')

@php
    $isEdit = isset($pageType);
@endphp

<div class="container-fluid px-4 py-5">
    <h2 class="mb-4 fw-bold text-primary">
        {{ $isEdit ? 'Editar Tipo de Página' : 'Criar Novo Tipo de Página' }}
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

    <form action="{{ $isEdit ? route('page-types.update', $pageType->id) : route('page-types.store') }}" method="POST" class="bg-white p-4 rounded shadow-sm">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="mb-3">
            <label for="name" class="form-label">Nome do Tipo de Página</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $isEdit ? $pageType->name : '') }}" required>
        </div>

        <hr>
        <h5>Campos Dinâmicos</h5>
        <div id="fields-wrapper">
            @php
                $fields = old('fields', $isEdit ? $pageType->fields->toArray() : []);
                $fieldIndex = 0;
            @endphp

            @foreach($fields as $index => $field)
            <div class="field-item mb-3 row align-items-end">
                <input type="hidden" name="fields[{{ $fieldIndex }}][id]" value="{{ $field['id'] ?? '' }}">

                <div class="col-md-2">
                    <input type="text" name="fields[{{ $fieldIndex }}][name]" class="form-control" placeholder="Nome do Campo" value="{{ $field['name'] ?? '' }}" required>
                </div>
                <div class="col-md-2">
                    <input type="text" name="fields[{{ $fieldIndex }}][label]" class="form-control" placeholder="Label" value="{{ $field['label'] ?? '' }}" required>
                </div>
                <div class="col-md-2">
                    <select name="fields[{{ $fieldIndex }}][type]" class="form-control field-type" required>
                        @foreach(['text','textarea','image','gallery','boolean','select','radio','page','date','datetime'] as $type)
                        <option value="{{ $type }}" @selected(($field['type'] ?? '') === $type)> {{ ucfirst($type) }} </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 page-type-select {{ ($field['type'] ?? '') === 'page' ? '' : 'd-none' }}">
                    <select name="fields[{{ $fieldIndex }}][page_type]" class="form-control">
                        <option value="">-- Tipo de Página --</option>
                        @foreach($allPageTypes as $pt)
                            <option value="{{ $pt->id }}" @selected(($field['page_type'] ?? null) == $pt->id)>{{ $pt->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <input type="number" name="fields[{{ $fieldIndex }}][order]" class="form-control" placeholder="Ordem" value="{{ $field['order'] ?? '' }}">
                </div>
                <div class="col-md-1 text-center">
                    <input type="checkbox" name="fields[{{ $fieldIndex }}][is_required]" value="1" @checked(!empty($field['is_required']))>
                    <label>Obrig?</label>
                </div>
                <div class="col-md-2">
                    <input type="text" name="fields[{{ $fieldIndex }}][options]" class="form-control options-field" placeholder="Opções (se aplicável)" value="{{ $field['options'] ?? '' }}">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger remove-field w-100">Remover</button>
                </div>
            </div>
            @php $fieldIndex++; @endphp
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
    let fieldIndex = {{ $fieldIndex ?? 0 }};

    document.getElementById('add-field').addEventListener('click', function() {
        const wrapper = document.getElementById('fields-wrapper');
        const fieldHTML = `
        <div class="field-item mb-3 row align-items-end">
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
            <div class="col-md-2 page-type-select d-none">
                <select name="fields[${fieldIndex}][page_type_id]" class="form-control">
                    <option value="">-- Tipo de Página --</option>
                    @foreach($allPageTypes as $pt)
                        <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <input type="number" name="fields[${fieldIndex}][order]" class="form-control" placeholder="Ordem">
            </div>
            <div class="col-md-1">
                <input type="checkbox" name="fields[${fieldIndex}][is_required]" value="1">
                <label>Obrig?</label>
            </div>
            <div class="col-md-2">
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

    document.addEventListener('change', function(e) {
        if(e.target.classList.contains('field-type')) {
            const fieldItem = e.target.closest('.field-item');
            const pageSelect = fieldItem.querySelector('.page-type-select');
            const optionsField = fieldItem.querySelector('.options-field');

            if(e.target.value === 'page') {
                pageSelect.classList.remove('d-none');
                optionsField.classList.add('d-none');
                optionsField.value = '';
            } else if(e.target.value === 'select' || e.target.value === 'radio') {
                pageSelect.classList.add('d-none');
                optionsField.classList.remove('d-none');
            } else {
                pageSelect.classList.add('d-none');
                optionsField.classList.add('d-none');
                optionsField.value = '';
            }
        }
    });
</script>

@endsection
