@extends('layouts.admin')

@section('main-content')

<div class="container-fluid px-4 py-5">
    <h2 class="mb-4 fw-bold text-primary">
        {{ isset($setting) ? 'Editar Configuração' : 'Criar Configuração' }}
    </h2>



    <form action="{{ isset($setting) ? route('settings.update', $setting) : route('settings.store') }}" class="bg-white p-4 rounded shadow-sm"
        method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($setting)) @method('PUT') @endif

        <div class="form-group">
            <label for="titulo">Título (nome visível) </label>
            <input type="text" class="form-control" id="titulo" name="titulo"
                value="{{ $setting->title ?? old('titulo') }}" required>
        </div>

        <div class="form-group">
            <label for="label">Label (chave interna)</label>
            <input type="text" class="form-control" id="label" name="label"
                value="{{ $setting->label ?? old('label') }}" required>
        </div>

        <div class="form-group">
            <label for="tipo">Tipo</label>
            <select class="form-control" id="tipo" name="tipo" onchange="toggleValorField()" required>
                @php $tipoAtual = old('tipo', $setting->type ?? 'text'); @endphp
                <option value="text" {{ $tipoAtual === 'text' ? 'selected' : '' }}>Texto</option>
                <option value="number" {{ $tipoAtual === 'number' ? 'selected' : '' }}>Número</option>
                <option value="boolean" {{ $tipoAtual === 'boolean' ? 'selected' : '' }}>Sim/Não</option>
                <option value="image" {{ $tipoAtual === 'image' ? 'selected' : '' }}>Imagem</option>
            </select>
        </div>

        {{-- Texto --}}
        <div class="form-group" id="field-texto">
            <label for="valor-texto">Valor</label>
            <input type="text" class="form-control" id="valor-texto" name="valor"
                value="{{ old('value', isset($setting) && $tipoAtual!=='imagem' ? $setting->value : '') }}">
        </div>

        {{-- Número --}}
        <div class="form-group d-none" id="field-numero">
            <label for="valor-numero">Valor numérico</label>
            <input type="number" step="any" class="form-control" id="valor-numero" name="valor"
                value="{{ old('valor', ($tipoAtual==='numero') ? $setting->valor ?? '' : '') }}">
        </div>

        {{-- Booleano --}}
        <div class="form-group d-none" id="field-booleano">
            <label for="valor-booleano">Valor (Sim/Não)</label>
            @php $boolVal = old('valor', isset($setting) ? (string)$setting->valor : '0'); @endphp
            <select class="form-control" id="valor-booleano" name="valor">
                <option value="1" {{ $boolVal === '1' ? 'selected' : '' }}>Sim</option>
                <option value="0" {{ $boolVal === '0' ? 'selected' : '' }}>Não</option>
            </select>
        </div>

        {{-- Imagem --}}
        <div class="form-group d-none" id="field-imagem">
            <label for="valor-imagem">Imagem</label>
            <input type="file" class="form-control" id="valor-imagem" name="valor" accept="image/*">
            @if(isset($setting) && $setting->type === 'image' && $setting->value)
            <div class="mt-2">
                <img src="{{ asset('storage/'.$setting->value) }}" alt="Imagem atual" style="max-width: 200px; height:auto;">
            </div>
            @endif
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <a href="{{ route('settings.index') }}" class="btn btn-danger px-4 py-2 rounded-pill shadow">Voltar</a>
            <button type="submit" class="btn btn-success px-4 py-2 rounded-pill shadow">
                <i class="bi bi-check-circle me-1"></i> Salvar
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function setVisible(containerId, visible) {
        const el = document.getElementById(containerId);
        if (!el) return;
        el.classList.toggle('d-none', !visible);
        // desativa/ativa inputs internos para evitar enviar múltiplos "valor"
        el.querySelectorAll('input, select, textarea').forEach(inp => {
            inp.disabled = !visible;
        });
    }

    function toggleValorField() {
        const tipo = document.getElementById('tipo').value;

        setVisible('field-texto', tipo === 'text');
        setVisible('field-numero', tipo === 'number');
        setVisible('field-booleano', tipo === 'boolean');
        setVisible('field-imagem', tipo === 'image');
    }

    // Garante que roda no load mesmo que o onchange não tenha sido disparado ainda
    document.addEventListener('DOMContentLoaded', function() {
        // Inicialmente desativamos todos, depois ativamos o escolhido
        ['field-texto', 'field-numero', 'field-booleano', 'field-imagem'].forEach(id => setVisible(id, false));
        toggleValorField();
    });
</script>
@endpush