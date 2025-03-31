@extends('layouts.admin')

@section('main-content')

    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Parceiros</h1>
        <a href="{{ route('partners.create') }}" class="btn btn-primary">Adicionar Novo Parceiro</a>
    </div>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Parceiros</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>Nome de Contacto</th>
                            <th>Email</th>
                            <th>Telemóvel</th>
                            <th>Morada</th>
                            <th>Accões</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($partners as $partner)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $partner->name }}</td>
                                <td>{{ $partner->contact_name }}</td>
                                <td>{{ $partner->email }}</td>
                                <td>{{ $partner->phone }}</td>
                                <td>{{ $partner->address }}</td>
                                <td>
                                    <a href="{{ route('partners.edit', $partner->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('partners.destroy', $partner->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem a certeza que deseja excluir este parceiro?')"><i class="fas fa-trash"></i></button>
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
