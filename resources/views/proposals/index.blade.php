@extends('layouts.admin')

@section('main-content')
<div class="container-fluid px-4 py-5">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 text-primary mb-0">Propostas</h1>
                <a href="{{ route('proposals.create') }}" class="btn btn-outline-primary shadow-sm">
                    <i class="fas fa-plus me-1"></i> Adicionar proposta
                </a>
            </div>

            <!-- Filtros -->
            <form method="GET" action="{{ route('proposals.index') }}" class="mb-4">
                <div class="row">
                    <!-- Filtro por Estado -->
                    <div class="col-md-4">
                        <label for="status">Estado</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">Todos</option>
                            <option value="Pendente" {{ request('status') == 'Pendente' ? 'selected' : '' }}>Pendente</option>
                            <option value="Aprovada" {{ request('status') == 'Aprovada' ? 'selected' : '' }}>Aprovada</option>
                            <option value="Reprovada" {{ request('status') == 'Reprovada' ? 'selected' : '' }}>Reprovada</option>
                            <option value="Enviado" {{ request('status') == 'Enviado' ? 'selected' : '' }}>Enviado</option>
                            <option value="Sem resposta" {{ request('status') == 'Sem resposta' ? 'selected' : '' }}>Sem resposta</option>
                        </select>
                    </div>

                    <!-- Filtro por Cliente -->
                    <div class="col-md-4">
                        <label for="client_id">Cliente</label>
                        <select name="client_id" id="client_id" class="form-control">
                            <option value="">Todos</option>
                            @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Botão de Filtrar -->
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                    </div>
                </div>
            </form>


            <div class="d-block d-md-none">
                @foreach($proposals as $proposal)
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">{{ $proposal->brand }} {{ $proposal->model }}</h5>
                        <p class="card-text mb-1"><strong>Cliente:</strong> {{ $proposal->client->name }}</p>
                        <p class="card-text mb-1"><strong>Versão:</strong> {{ $proposal->version }}</p>
                        <p class="card-text mb-1"><strong>Estado:</strong> 
                            <select class="form-select form-select-sm status-dropdown" data-id="{{ $proposal->id }}">
                                <option value="Pendente" {{ $proposal->status == 'Pendente' ? 'selected' : '' }}>Pendente</option>
                                <option value="Aprovada" {{ $proposal->status == 'Aprovada' ? 'selected' : '' }}>Aprovada</option>
                                <option value="Reprovada" {{ $proposal->status == 'Reprovada' ? 'selected' : '' }}>Reprovada</option>
                                <option value="Enviado" {{ $proposal->status == 'Enviado' ? 'selected' : '' }}>Enviado</option>
                                <option value="Sem resposta" {{ $proposal->status == 'Sem resposta' ? 'selected' : '' }}>Sem resposta</option>
                            </select>
                        </p>
                        <p class="card-text mb-2"><strong>Combustível:</strong> {{ $proposal->fuel }}</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('proposals.edit', $proposal) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('proposals.destroy', $proposal) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta proposta?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                            <a href="{{ route('proposals.downloadPdf', $proposal->id) }}" class="btn btn-sm btn-primary"><i class="fas fa-download"></i></a>
                            <a href="{{ route('proposals.detail', [
                                'brand' => Str::slug($proposal->brand),
                                'model' => Str::slug($proposal->model),
                                'version' => Str::slug($proposal->version),
                                'id' => $proposal->id
                            ]) }}" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a>
                            <form action="{{ route('proposals.duplicate', $proposal->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-secondary"><i class="fas fa-copy"></i></button>
                            </form>
                            <form action="{{ route('proposals.sent-whatsapp', $proposal->id) }}" method="GET">
                                <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-whatsapp"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="table-responsive  d-none d-md-block">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Cliente</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Versão</th>
                            <th>Estado</th>
                            <th>Combustível</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($proposals as $proposal)
                        @if($proposal)
                        <tr>
                            <td>{{ $proposal->client->name }}</td>
                            <td>{{ $proposal->brand }} </td>
                            <td>{{ $proposal->model }}</td>
                            <td>{{ $proposal->version }}</td>
                            <td>
                                <select class="form-select form-select-sm status-dropdown" data-id="{{ $proposal->id }}">
                                    <option value="Pendente" {{ $proposal->status == 'Pendente' ? 'selected' : '' }}>Pendente</option>
                                    <option value="Aprovada" {{ $proposal->status == 'Aprovada' ? 'selected' : '' }}>Aprovada</option>
                                    <option value="Reprovada" {{ $proposal->status == 'Reprovada' ? 'selected' : '' }}>Reprovada</option>
                                    <option value="Enviado" {{ $proposal->status == 'Enviado' ? 'selected' : '' }}>Enviado</option>
                                    <option value="Sem resposta" {{ $proposal->status == 'Sem resposta' ? 'selected' : '' }}>Sem resposta</option>
                                </select>
                            </td>
                            <td>{{ $proposal->fuel }}</td>
                            <td>
                                <div class="btn-group" role="group" aria-label="Ações">
                                    <a href="{{ route('proposals.edit', $proposal) }}" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('proposals.destroy', $proposal) }}" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir esta proposta?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('proposals.downloadPdf', $proposal->id) }}" class="btn btn-sm btn-primary" title="Download PDF">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <a href="{{ route('proposals.detail', [
    'brand' => Str::slug($proposal->brand),
    'model' => Str::slug($proposal->model),
    'version' => Str::slug($proposal->version),
    'id' => $proposal->id
]) }}" class="btn btn-sm btn-primary" title="Ver Proposta">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="{{ route('proposals.duplicate', $proposal->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-secondary" title="Duplicar">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('proposals.sent-whatsapp', $proposal->id) }}" method="GET" style="display:inline;">
                                        <button type="submit" class="btn btn-sm btn-success" title="Enviar WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">
                {{ $proposals->withQueryString()->links() }}
            </div>
        </div>
    </div>


    <script>
        document.querySelectorAll('.status-dropdown').forEach(dropdown => {
            dropdown.addEventListener('change', function() {
                const proposalId = this.getAttribute('data-id');
                const newStatus = this.value;

                fetch(`/proposals/${proposalId}/update-status`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            status: newStatus
                        }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.success);
                    })
                    .catch(error => {
                        console.error('Erro ao atualizar o estado:', error);
                        alert('Erro ao atualizar o estado.');
                    });
            });
        });
    </script>
    @endsection