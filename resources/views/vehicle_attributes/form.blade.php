@extends('layouts.admin')

@section('main-content')
<div class="container-fluid px-4 py-5">
  <h2 class="mb-4 fw-bold text-primary">{{ isset($vehicle) ? 'Editar Atributo' : 'Criar Novo Atributo' }}</h2>

  @if (session('success'))
  <div class="alert alert-success">
    {{ session('success') }}
  </div>
  @endif
  @if (session('error'))
  <div class="alert alert-danger">
    {{ session('error') }}
  </div>
  @endif
  @if ($errors->any())
  <div class="alert alert-danger">
    <ul>
      @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif
  <form method="POST" action="{{ isset($vehicleAttribute) ? route('vehicle-attributes.update', $vehicleAttribute) : route('vehicle-attributes.store') }}" class="bg-white p-4 rounded shadow-sm">
    @csrf
    @if(isset($vehicleAttribute)) @method('PUT') @endif
    <div class="row g-3">

      <div class="form-group col-md-2 mt-4">
        <label>Nome</label>
        <input type="text" id="nameInput" name="name" value="{{ old('name', $vehicleAttribute->name ?? '') }}" class="form-control rounded shadow-sm">
      </div>

      <div class="form-group col-md-2 mt-4">
        <label>Chave</label>
        <input type="text" id="keyInput" name="key" value="{{ old('key', $vehicleAttribute->key ?? '') }}" class="form-control rounded shadow-sm" readonly>
      </div>

      <div class="form-group col-md-2 mt-4">
        <label>Tipo</label>
        <select name="type" id="type" class="form-control rounded shadow-sm">
          <option value="">Selecione o tipo</option>
          @foreach(['text','number','boolean','select'] as $type)
          <option value="{{ $type }}" {{ old('type', $vehicleAttribute->type ?? '') == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
          @endforeach
        </select>
      </div>

      <div class="form-group col-md-2 mt-4" id="options-container" style="display: none;">
        <label>Opções</label>
        <input type="text" name="options" value="{{ old('options', isset($vehicleAttribute) && $vehicleAttribute->options ? implode(',', json_decode($vehicleAttribute->options)) : '') }}" class="form-control rounded shadow-sm">
      </div>

      <div class="form-group col-md-2 mt-4">
        <label>Grupo de Atributos</label>Dados da Viatura
        <select name="attribute_group" id="attribute_group" class="form-control rounded shadow-sm">
          <option value="">Selecione o Grupo</option>

          @foreach(['Equipamento', 'Segurança & Desempenho', 'Equipamento Interior', 'Equipamento Exterior', 'Conforto & Multimédia', 'Dados do Veículo','Características Técnicas', 'Outros Extras'] as $attribute_group)
          <option value="{{ $attribute_group }}" {{ old('attribute_group', $vehicleAttribute->attribute_group ?? '') == $attribute_group ? 'selected' : '' }}>{{ ucfirst($attribute_group) }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-2 mt-4">
        <label>Ordem</label>
        <input type="number" name="order" value="{{ old('order', $vehicleAttribute->order ?? '') }}" class="form-control rounded shadow-sm">
      </div>
    </div>



    <div class="mt-4 d-flex justify-content-between">
      <a href="{{ route('vehicles.index') }}" class="btn btn-danger px-4 py-2 rounded-pill shadow">Voltar</a>
      <button type="submit" class="btn btn-success px-4 py-2 rounded-pill shadow">
        <i class="bi bi-check-circle me-1"></i> Salvar
      </button>
    </div>
  </form>
</div>
@endsection

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const optionsContainer = document.getElementById('options-container');

    function toggleOptionsField() {
      if (typeSelect.value === 'select') {
        optionsContainer.style.display = 'block';
      } else {
        optionsContainer.style.display = 'none';
      }
    }

    typeSelect.addEventListener('change', toggleOptionsField);

    // Run on page load in case "select" is already selected (e.g. in edit form)
    toggleOptionsField();
  });
  // Função para gerar o slug
  function slugify(text) {
    return text.toString().toLowerCase()
      .normalize('NFD') // remove acentos
      .replace(/[\u0300-\u036f]/g, '') // remove acentos
      .replace(/\s+/g, '_') // substitui espaços por underline
      .replace(/[^\w\-]+/g, '') // remove caracteres não alfanuméricos
      .replace(/\_\_+/g, '_') // remove underline duplicados
      .replace(/^_+/, '') // remove underline no começo
      .replace(/_+$/, ''); // remove underline no fim
  }

  // Espera o DOM estar completamente carregado
  document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('nameInput');
    const keyInput = document.getElementById('keyInput');

    // Verifica se os elementos existem antes de adicionar o ouvinte de evento
    if (nameInput && keyInput) {
      nameInput.addEventListener('input', function() {
        keyInput.value = slugify(this.value);
      });
    }
  });
</script>