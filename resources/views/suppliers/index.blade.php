@extends('layouts.admin')

@section('main-content')
<div class="container-fluid px-4 py-5">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 text-primary mb-0">Fornecedores</h1>
                <a href="{{ route('suppliers.create') }}" class="btn btn-outline-primary shadow-sm">
                    <i class="fas fa-plus me-1"></i> Adicionar Fornecedor
                </a>
            </div>

            <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mt-3">
                <thead class="table-dark">
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
                            <div class="btn-group" role="group" aria-label="Ações">
                                <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-sm btn-outline-secondary me-2" title="Editar fornecedor">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir este fornecedor?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar fornecedor">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            <div class="d-flex justify-content-center">
                {{ $suppliers->links() }}
            </div>
        </div>
    </div>
</div>
@endsection