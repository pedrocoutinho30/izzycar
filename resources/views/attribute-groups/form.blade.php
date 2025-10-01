@extends('layouts.admin')

@section('main-content')
<div class="container">
    <h1>{{ isset($attributeGroup) ? 'Editar Grupo' : 'Criar Novo Grupo' }}</h1>

    <form action="{{ isset($attributeGroup) ? route('attribute-groups.update', $attributeGroup->id) : route('attribute-groups.store') }}" method="POST">
        @csrf
        @if(isset($attributeGroup))
            @method('PUT')
        @endif

        <div class="mb-3">
            <label for="name" class="form-label">Nome do Grupo</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $attributeGroup->name ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label for="order" class="form-label">Ordem</label>
            <input type="number" name="order" id="order" class="form-control" value="{{ old('order', $attributeGroup->order ?? 0) }}" required>
        </div>

        <button type="submit" class="btn btn-success">Salvar</button>
        <a href="{{ route('attribute-groups.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
