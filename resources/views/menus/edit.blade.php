@extends('layouts.admin')
@section('main-content')
<div class="container py-5">
    <h2 class="fw-bold mb-4 text-primary">Editar Menu</h2>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('menus.update', $menu) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Título</label>
            <input type="text" name="title" value="{{ old('title', $menu->title) }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>URL (deixar vazio se for dropdown)</label>
            <input type="text" name="url" value="{{ old('url', $menu->url) }}" class="form-control">
        </div>

        <div class="mb-3">
            <label>Menu Pai (opcional - apenas 1 nível)</label>
            <select name="parent_id" class="form-select">
                <option value="">-- Nenhum (menu principal) --</option>
                @foreach($parents as $parent)
                <option value="{{ $parent->id }}" {{ $menu->parent_id == $parent->id ? 'selected' : '' }}>
                    {{ $parent->title }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Ordem</label>
            <input type="number" name="order" value="{{ old('order', $menu->order) }}" class="form-control">
        </div>

          <div class="mb-3">
            <label>Ordem</label>
            <input type="number" name="order" value="{{ old('order', $menu->order) }}" class="form-control">
        </div>

        <div class="mb-3">
            <label>Mostrar Online</label>
            <select name="show_online" class="form-control">
                <option value="1" {{ $menu->show_online ? 'selected' : '' }}>Sim</option>
                <option value="0" {{ !$menu->show_online ? 'selected' : '' }}>Não</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Guardar Alterações</button>
        <a href="{{ route('menus.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>


@endsection