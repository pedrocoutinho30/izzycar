@extends('layouts.admin')

@section('main-content')
<div class="container-fluid px-4 py-5">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 text-primary mb-0">Formulários de Proposta</h1>
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
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($forms as $form)
                        @if($form)
                        <tr>

                            <td>{{ $form->name ?? 'N/A' }}</td>
                            <td>{{ $form->brand ?? 'N/A' }}</td>
                            <td>{{ $form->model ?? 'N/A' }}</td>
                            <td>{{ $form->version ?? 'N/A' }}</td>
                            <td>{{ $form->status ?? 'N/A' }}</td>
                            <td>
                                <div class="btn-group" role="group" aria-label="Ações">
                                    <a href="{{ route('form_proposals.show', $form->id) }}" class="btn btn-sm btn-outline-secondary me-2" title="Ver detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection