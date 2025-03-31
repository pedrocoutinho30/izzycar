@extends('layouts.admin')

@section('main-content')
<div class="container">
    <h1>Lista de Marcas e Modelos</h1>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Marca</th>
                <th>Referência</th>
                <th>Modelos</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($brands as $brand)
            <tr>
                <td>{{ $brand->name }}</td>
                <td>{{ $brand->reference }}</td>
                <td>
                    <ul>
                        @foreach ($brand->models as $model)
                        <li>{{ $model->name }} ({{ $model->reference }})</li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection