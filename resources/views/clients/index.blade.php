@extends('layouts.admin')

@section('main-content')
<h1 class="h3 mb-2 text-gray-800">Clientes</h1>
<a href="{{ route('clients.create') }}" class="btn btn-primary mb-3">Novo Cliente</a>
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Telefone</th>
                        <th>Origem</th>
                        <th>Cidade</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($clients as $client)
                    <tr>
                        <td>{{ $client->name }}</td>
                        <td>{{ $client->email }}</td>
                        <td>{{ $client->phone }}</td>
                        <td>{{ $client->origin }}</td>
                        <td>{{ $client->city }}</td>
                        <td>
                            <!-- <a href="{{ route('clients.show', $client->id) }}" class="btn btn-info btn-sm">Ver</a> -->
                            <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a
                                <form action="{{ route('clients.destroy', $client->id) }}" method="POST" style="display:inline;">
                            <form action="{{ route('clients.destroy', $client->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem a certeza que deseja excluir este cliente?')"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection