@extends('layouts.admin')
@section('main-content')
<div class="container py-5">
    <h2 class="fw-bold mb-4 text-primary">Criar Menu</h2>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('menus.store') }}" method="POST">
        @csrf
        <form method="POST" action="{{ route('menus.store') }}">
    @csrf
    <div class="mb-3">
        <label>Título</label>
        <input type="text" name="title" class="form-control" required>
    </div>
    
    <div class="mb-3">
        <label>URL (deixar vazio se for dropdown)</label>
        <input type="text" name="url" class="form-control">
    </div>
    
    <div class="mb-3">
        <label>Menu Pai (opcional - apenas 1 nível)</label>
        <select name="parent_id" class="form-select">
            <option value="">-- Nenhum (menu principal) --</option>
            @foreach($parents as $parent)
                <option value="{{ $parent->id }}">{{ $parent->title }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label>Ordem</label>
        <input type="number" name="order" class="form-control" value="0">
    </div>
    
    <button type="submit" class="btn btn-success">Guardar</button>
</form>
    </form>
</div>
@endsection