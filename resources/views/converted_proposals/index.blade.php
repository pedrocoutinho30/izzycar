@extends('layouts.admin')

@section('main-content')
<div class="container-fluid px-4 py-5">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 text-primary mb-0">Propostas Convertidas</h1>
            </div>
            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($proposals->isEmpty())
            <p>Sem propostas convertidas ainda. </p>
            @else
            <div class="table-responsive  d-none d-md-block">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Car</th>
                            <th>Status</th>
                            <th>Valor Carro</th>
                            <th>Carro Pago</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($proposals as $proposal)
                        <tr>
                            <td>{{ $proposal->id }}</td>
                            <td>{{ $proposal->client->name ?? 'N/A' }}</td>
                            <td>{{ $proposal->brand }} {{ $proposal->modelCar }} {{ $proposal->version }}</td>
                            <td>{{ $proposal->status }}</td>
                            <td>{{ number_format($proposal->valor_carro, 2, ',', '.') }} €</td>
                            <td>{{ $proposal->carro_pago ? 'Sim' : 'Não' }}</td>
                            <td>
                                <a href="{{ route('converted-proposals.edit', $proposal->id) }}" class="btn btn-sm btn-outline-secondary me-2">
                                    <li class="fas fa-edit"></li>
                                </a>
                                <a href="{{ route('converted-proposals.timeline', [$proposal->brand, $proposal->modelCar, $proposal->version, $proposal->id]) }}" class="btn btn-sm btn-info">
                                    <li class="fa fa-eye"></li>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $proposals->links() }} <!-- Paginação -->
            @endif
        </div>
    </div>
</div>
@endsection