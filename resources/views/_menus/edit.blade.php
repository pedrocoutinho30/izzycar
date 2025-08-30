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
            <label for="name" class="form-label">Nome do Menu</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $menu->name) }}" required>
        </div>

        <div class="mb-3">
            <label for="name" class="form-label">Label</label>
            <input type="text" disabled name="slug" id="slug" class="form-control" value="{{ old('slug', $menu->slug) }}" required>
            <input type="hidden" name="slug" id="slug" value="{{ $menu->slug }}">
        </div>

        <div class="d-flex justify-content-between mb-3">
            <h5>Itens do Menu</h5>
            <button type="button" class="btn btn-sm btn-primary" onclick="addMenuItem()">+ Adicionar Item</button>
        </div>

        <div id="menu-items-wrapper">
            @foreach ($menu->items as $index => $item)
            <div class="card mb-2 p-3 menu-item">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Texto</label>
                        <input type="text" name="items[{{ $index }}][title]" class="form-control" value="{{ $item->title }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Label</label>
                        <input type="text" disabled name="items[{{ $index }}][item_label]" class="form-control" value="{{ $item->url }}" required>
                        <input type="hidden" name="items[{{ $index }}][item_label]" value="{{ $item->url }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Página</label>
                        <select name="items[{{ $index }}][page_id]" class="form-control" required>
                            <option value="">Selecione uma página</option>
                            @foreach ($pages as $page)
                            <option value="{{ $page->id }}" {{ isset($item->page_id) && $item->page_id == $page->id ? 'selected' : '' }}>
                                {{ $page->title }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Ordem</label>
                        <input type="number" name="items[{{ $index }}][order]" class="form-control" value="{{ $item->order }}">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger" onclick="this.closest('.menu-item').remove()">Remover</button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-4">
            <a href="{{ route('menus.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success">Guardar</button>
        </div>
    </form>
</div>

<script>
    let itemIndex = {{$menu->items->count()}};

    function addMenuItem() {
        const wrapper = document.getElementById('menu-items-wrapper');
        const template = `
            <div class="card mb-2 p-3 menu-item">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="items[${itemIndex}][title]" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Label</label>
                        <input type="text" name="items[${itemIndex}][item_label]" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Página</label>
                        <select name="items[${itemIndex}][page_id]" class="form-control" required>
                            <option value="">Selecione uma página</option>
                            @foreach ($pages as $page)
                                <option value="{{ $page->id }}">{{ $page->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Ordem</label>
                        <input type="number" name="items[${itemIndex}][order]" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger" onclick="this.closest('.menu-item').remove()">Remover</button>
                    </div>
                </div>
            </div>
        `;
        wrapper.insertAdjacentHTML('beforeend', template);
        itemIndex++;
    }
</script>
@endsection