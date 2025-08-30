@extends('layouts.admin')
@section('main-content')


<div class="container-fluid px-4 py-5">
    <h2 class="mb-4 fw-bold text-primary">
        Criar Novo Tipo de Página
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

    <form action="{{ route('page-types.store') }}" method="POST" class="bg-white p-4 rounded shadow-sm">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Nome do Tipo</label>
            <input type="text" class="form-control rounded shadow-sm" id="name" name="name" placeholder="Ex: Página Inicial" required>
        </div>

        <hr>

        <h5 class="mb-3">Campos Dinâmicos</h5>
        <div id="fields-wrapper">
            <div class="field-item mb-3 row align-items-end">
                <div class="col-md-3">
                    <input type="text" name="fields[0][name]" class="form-control rounded shadow-sm" placeholder="Nome do Campo" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="fields[0][label]" class="form-control rounded shadow-sm" placeholder="Label do Campo" required>
                </div>
                <div class="col-md-2">
                    <select name="fields[0][type]" class="form-control rounded shadow-sm field-type" required>
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
                    <input type="number" name="fields[0][order]" class="form-control rounded shadow-sm" placeholder="Ordem" min="0">
                </div>
                <div class="col-md-1 text-center">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="fields[0][is_required]" value="1" id="required_0">
                        <label class="form-check-label" for="required_0">Obrigatório</label>
                    </div>
                </div>
                <div class="col-md-1">
                    <input type="text" name="fields[0][options]" class="form-control rounded shadow-sm options-field d-none" placeholder="Opções (separadas por vírgula)">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger remove-field w-100">Remover</button>
                </div>
            </div>
        </div>

        <button type="button" class="btn btn-secondary mb-4" id="add-field">+ Adicionar Campo</button>

        <div class="d-flex justify-content-end">
            <a href="{{ route('page-types.index') }}" class="btn btn-danger px-4 py-2 rounded-pill shadow">Voltar</a>
            <button type="submit" class="btn btn-success px-4 py-2 rounded-pill shadow">
                <i class="bi bi-check-circle me-1"></i> Salvar
            </button>
        </div>
    </form>
</div>

<script>
    let fieldIndex = 1;

   document.getElementById('add-field').addEventListener('click', function() {
        const wrapper = document.getElementById('fields-wrapper');
        const fieldHTML = `
            <div class="field-item mb-3 row align-items-end">
                <div class="col-md-3">
                    <input type="text" name="fields[${fieldIndex}][name]" class="form-control rounded shadow-sm" placeholder="Nome do Campo" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="fields[${fieldIndex}][label]" class="form-control rounded shadow-sm" placeholder="Label do Campo" required>
                </div>
                <div class="col-md-2">
                    <select name="fields[${fieldIndex}][type]" class="form-control rounded shadow-sm field-type" required>
                        <option value="">Tipo de Campo</option>
                        <option value="text">Texto</option>
                        <option value="textarea">Área de Texto</option>
                        <option value="image">Imagem</option>
                        <option value="gallery">Galeria</option>
                        <option value="boolean">Ativo/Inativo</option>
                        <option value="select">Select</option>
                        <option value="radio">Radio</option>
                          <option value="page">Páginas</option>
                        <option value="date">Data</option>
                        <option value="datetime">Data e Hora</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <input type="number" name="fields[${fieldIndex}][order]" class="form-control rounded shadow-sm" placeholder="Ordem" min="0">
                </div>
                <div class="col-md-1 text-center">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="fields[${fieldIndex}][is_required]" value="1" id="required_${fieldIndex}">
                        <label class="form-check-label" for="required_${fieldIndex}">Obrigatório</label>
                    </div>
                </div>
                <div class="col-md-1">
                    <input type="text" name="fields[${fieldIndex}][options]" class="form-control rounded shadow-sm options-field d-none" placeholder="Opções">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger remove-field w-100">Remover</button>
                </div>
            </div>
        `;
        wrapper.insertAdjacentHTML('beforeend', fieldHTML);
        fieldIndex++;
        toggleOptionsVisibility(); // reexecuta após adicionar novo campo
    });

    // Remoção dinâmica de campos

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-field')) {
            e.target.closest('.field-item').remove();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        function toggleOptionsVisibility() {
            document.querySelectorAll('.field-item').forEach((item, index) => {
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

        toggleOptionsVisibility(); // run on load in case of repopulated forms
    });
</script>
@endsection