@extends('layouts.admin')

@section('main-content')
<div class="container">
    <h2>Lista de Despesas</h2>

    <a href="{{ route('expenses.create') }}" class="btn btn-primary mb-3">Criar Nova Despesa</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Título</th>
                <th>Tipo</th>
                <th>Valor</th>
                <th>Taxa de IVA</th>
                <th>Data da Despesa</th>
                <th>Parceiro</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $expense)
            <tr>
                <td>{{ $expense->id }}</td>
                <td>{{ $expense->title }}</td>
                <td>{{ $expense->type }}</td>
                <td>{{ number_format($expense->amount, 2, ',', '.') }}€</td>
                <td>{{ $expense->vat_rate }}</td>
                <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d/m/Y') }}</td>
                <td>{{ $expense->partner ? $expense->partner->name : 'N/A' }}</td>
                <td>
                    <a href="{{ route('expenses.edit', $expense->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                    <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir?')"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $expenses->links() }} <!-- Paginação -->
</div>
@endsection
