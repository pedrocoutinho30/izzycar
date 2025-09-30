@extends('layouts.admin')

@section('main-content')
<div class="container-fluid px-4 py-5">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 text-primary mb-0">Parceiros</h1>
                <a href="{{ route('partners.create') }}" class="btn btn-outline-primary shadow-sm">
                    <i class="fas fa-plus me-1"></i> Adicionar Parceiro
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle" id="dataTable" width="100%" cellspacing="0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>Nome de Contacto</th>
                            <th>Email</th>
                            <th>Telemóvel</th>
                            <th>Morada</th>
                            <th>Ações</th>
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
                                <div class="btn-group" role="group" aria-label="Ações">
                                    <a href="{{ route('partners.edit', $partner->id) }}" class="btn btn-sm btn-outline-secondary me-2" title="Editar parceiro">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('partners.destroy', $partner->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Tem a certeza que deseja excluir este parceiro?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar parceiro">
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
                {{ $partners->links() }}
            </div>
        </div>
    </div>
</div>

@endsection