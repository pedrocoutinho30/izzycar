@extends('layouts.admin')

@section('main-content')
<h1 class="h3 mb-2 text-gray-800">Fornecedores</h1>
<a href="{{ route('suppliers.create') }}" class="btn btn-primary mb-3">Adicionar Fornecedor</a>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Nome da Empresa</th>
            <th>Nome de Contacto</th>
            <th>Email</th>
            <th>Telefone</th>
            <th>Cidade</th>
            <th>País</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($suppliers as $supplier)
            <tr>
                <td>{{ $supplier->company_name }}</td>
                <td>{{ $supplier->contact_name }}</td>
                <td>{{ $supplier->email }}</td>
                <td>{{ $supplier->phone }}</td>
                <td>{{ $supplier->city }}</td>
                <td>{{ $supplier->country }}</td>
                <td>
                    <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                    <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
