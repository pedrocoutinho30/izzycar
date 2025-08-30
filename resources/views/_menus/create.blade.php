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
        <div class="mb-3">
            <label for="name" class="form-label">Nome do Menu</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
        </div>

        <div class="mb-3">
            <label for="slug" class="form-label">Label</label>
            <input type="text" name="slug" id="slug" class="form-control" value="{{ old('slug') }}" required readonly>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const nameInput = document.getElementById('name');
                const slugInput = document.getElementById('slug');
                nameInput.addEventListener('input', function () {
                    let value = nameInput.value
                        .toLowerCase()
                        .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // Remove acentos
                        .replace(/ç/g, 'c') // Troca ç por c
                        .replace(/\s+/g, '_')      // Substitui espaços por _
                        .replace(/[^a-z0-9_]/g, ''); // Remove caracteres não alfanuméricos exceto _
                    slugInput.value = value;
                });
            });
        </script>
        <a href="{{ route('menus.index') }}" class="btn btn-danger">Cancelar</a>
        <button type="submit" class="btn btn-success">Salvar</button>
    </form>
</div>
@endsection