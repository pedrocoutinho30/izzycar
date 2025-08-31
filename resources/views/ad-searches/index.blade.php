@extends('layouts.admin')

@section('main-content')
<div class="container-fluid px-4 py-5">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 text-primary mb-0">Análise de Mercado</h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('ad-searches.form') }}" class="btn btn-outline-primary shadow-sm">
                        <i class="fas fa-plus me-1"></i> Adicionar Análise
                    </a>
                    <a href="{{ route('ad-searches.importarAnuncios') }}" class="btn btn-outline-secondary shadow-sm" title="Importar Anúncios">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </div>
            </div>
            <div class="table-responsive">
            <table class="table table-striped table-hover align-middle" id="dataTable" width="100%" cellspacing="0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Sub Modelo</th>
                        <th>Ano</th>
                        <th>Combustível</th>
                        <th>Número de Anúncios</th>
                        <th>Preço Médio</th>
                        <th>Descrição</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($searches as $search)
                    <tr>
                        <td>{{ $search->id }}</td>
                        <td>{{ $search->brand }}</td>
                        <td>{{ $search->model }}</td>
                        <td>{{ $search->submodel }}</td>
                        <td>{{ $search->year_start }} - {{ $search->year_end }}</td>
                        <td>{{ $search->fuel }}</td>
                        <td>{{ $search->listings->count() }}</td>
                        <td>{{ number_format($search->listings->avg('price'), 0, ',', '.') }}</td>
                        <td>{{ $search->description }}</td>
                        <td>
                            <a href="{{ route('ad-searches.show', $search) }}" class="btn btn-info btn-sm" title="Ver detalhes">
                                <i class="fas fa-eye"></i>
                            </a>

                            <form action="{{ route('ad-searches.destroy', $search) }}" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir esta análise?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            <div class="d-flex justify-content-center">
                {{ $searches->links() }}
            </div>
        </div>
    </div>
</div>

@endsection