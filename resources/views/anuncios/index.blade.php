@extends('layouts.admin')

@section('main-content')
<div class="container">
    <h2>Importar Ficheiro de Anúncios</h2>
    <form action="{{ route('anuncios.importar') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="ficheiro" required>
        <button type="submit" class="btn btn-primary mt-2">Importar</button>
    </form>
</div>
@endsection
