<!-- resources/views/proposals/form.blade.php -->
@extends('layouts.admin')

@section('main-content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-fluid px-4 py-5">
    <h2 class="mb-4 fw-bold text-primary">
        Editar Proposta Convertida
    </h2>
    <form action="{{ route('converted-proposals.update', $convertedProposal->id) }}" method="POST" class="bg-white p-4 rounded shadow-sm">
        @csrf
        @method('PUT')
        {{-- STATUS --}}
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="status" class="form-label">Estado</label>
                <select name="status" id="status" class="form-control"
                    data-id="{{ $convertedProposal->id }}">
                    @foreach(['Iniciada','Negociação Carro','Transporte','IPO','DAV','ISV','Matriculação','IMT','Entrega','Registo automóvel','Concluido','Cancelado'] as $status)
                    <option value="{{ $status }}" {{ $convertedProposal->status === $status ? 'selected' : '' }}>
                        {{ $status }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div id="statusResponse" class="mt-2"></div>



            {{-- URL --}}
            <div class="col-md-8 mb-3">
                <label for="url" class="form-label">URL</label>
                <input type="url" name="url" id="url" class="form-control" value="{{ $convertedProposal->url }}">
                @if($convertedProposal->url)
                <small class="d-block mt-1">
                    <a href="{{ $convertedProposal->url }}" target="_blank">
                        {{ $convertedProposal->url }}
                    </a>
                </small>
                @endif
            </div>
        </div>

        @foreach($status_history as $history)
        <div class="alert alert-info">
            <strong>Alterado em:</strong> {{ $history->created_at }} <br>
            <strong>De:</strong> {{ $history->old_status }} <br>
            <strong>Para:</strong> {{ $history->new_status }}
        </div>
        @endforeach
        {{-- CLIENT & PROPOSAL IDs (readonly) --}}
        <div class="row g-3">
            <div class="col-md-1">
                <label class="form-label">Client ID</label>
                <input type="number" class="form-control" name="client_id" value="{{ $convertedProposal->client_id }}" readonly>
            </div>
            @php
            $client = App\Models\Client::find($convertedProposal->client_id);
            @endphp
            <div class="col-md-3">
                <label class="form-label">Nome Cliente</label>

                <input type="text" class="form-control" value="{{ $client->name ?? '' }}" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">Contacto Cliente</label>
                <input type="text" class="form-control" value="{{ $client->phone ?? '' }}" readonly>
            </div>

            <div class="col-md-1">
                <label class="form-label">Proposal ID</label>
                <input type="number" class="form-control" name="proposal_id" value="{{ $convertedProposal->proposal_id }}" readonly>
                @if($convertedProposal->url)
                <small class="d-block mt-1">
                    <a target="_blank" href="{{ route('proposals.edit', $convertedProposal->proposal_id) }}">
                        Ver Proposta
                    </a>
                </small>
                @endif
            </div>

        </div>

        <hr>

        {{-- CAR INFO --}}
        <div class="row g-3">
            <div class="col-md-1">
                <label class="form-label">Marca</label>
                <input type="text" readonly name="brand" class="form-control" value="{{ $convertedProposal->brand }}">
            </div>
            <div class="col-md-1">
                <label class="form-label">Modelo</label>
                <input type="text" readonly name="modelCar" class="form-control" value="{{ $convertedProposal->modelCar }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Versão</label>
                <input type="text" disabled name="version" class="form-control" value="{{ $convertedProposal->version }}">
            </div>
            <div class="col-md-1">
                <label class="form-label">Ano</label>
                <input type="text" readonly name="year" class="form-control" value="{{ $convertedProposal->year }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">KM</label>
                <input type="number" readonly name="km" class="form-control" value="{{ $convertedProposal->km }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Matrícula Origem</label>
                <input type="text" name="matricula_origem" class="form-control" value="{{ $convertedProposal->matricula_origem }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Matrícula Destino</label>
                <input type="text"
                    name="matricula_destino"
                    class="form-control"
                    value="{{ $convertedProposal->matricula_destino }}"
                    style="text-transform: uppercase;">
            </div>
        </div>

        <hr>

        {{-- CUSTOS + PAGAMENTOS --}}
        <div class="row g-3">
            {{-- Inspeção Origem --}}
            <div class="col-md-4">
                <label class="form-label">Custo Inspeção Origem</label>
                <input type="number" step="0.01" name="custo_inspecao_origem" class="form-control" value="{{ $convertedProposal->custo_inspecao_origem }}">
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" name="inspecao_origem_pago" value="1" {{ $convertedProposal->inspecao_origem_pago ? 'checked' : '' }}>
                    <label class="form-check-label">Pago</label>
                </div>
            </div>

            {{-- Transporte --}}
            <div class="col-md-4">
                <label class="form-label">Custo Transporte</label>
                <input type="number" step="0.01" name="custo_transporte" class="form-control" value="{{ $convertedProposal->custo_transporte }}">
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" name="transporte_pago" value="1" {{ $convertedProposal->transporte_pago ? 'checked' : '' }}>
                    <label class="form-check-label">Pago</label>
                </div>
            </div>

            {{-- IPO --}}
            <div class="col-md-4">
                <label class="form-label">Custo IPO</label>
                <input type="number" step="0.01" name="custo_ipo" class="form-control" value="{{ $convertedProposal->custo_ipo }}">
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" name="ipo_pago" value="1" {{ $convertedProposal->ipo_pago ? 'checked' : '' }}>
                    <label class="form-check-label">Pago</label>
                </div>
            </div>

            {{-- ISV --}}
            <div class="col-md-4">
                <label class="form-label">ISV</label>
                <input type="number" step="0.01" name="isv" class="form-control" value="{{ $convertedProposal->isv }}">
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" name="isv_pago" value="1" {{ $convertedProposal->isv_pago ? 'checked' : '' }}>
                    <label class="form-check-label">Pago</label>
                </div>
            </div>

            {{-- IMT --}}
            <div class="col-md-4">
                <label class="form-label">Custo IMT</label>
                <input type="number" step="0.01" name="custo_imt" class="form-control" value="{{ $convertedProposal->custo_imt }}">
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" name="imt_pago" value="1" {{ $convertedProposal->imt_pago ? 'checked' : '' }}>
                    <label class="form-check-label">Pago</label>
                </div>
            </div>

            {{-- Matrícula --}}
            <div class="col-md-4">
                <label class="form-label">Custo Matrícula</label>
                <input type="number" step="0.01" name="custo_matricula" class="form-control" value="{{ $convertedProposal->custo_matricula }}">
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" name="matricula_pago_impressa" value="1" {{ $convertedProposal->matricula_pago_impressa ? 'checked' : '' }}>
                    <label class="form-check-label">Pago e Impressa</label>
                </div>
            </div>

            {{-- Registo Automóvel --}}
            <div class="col-md-4">
                <label class="form-label">Custo Registo Automóvel</label>
                <input type="number" step="0.01" name="custo_registo_automovel" class="form-control" value="{{ $convertedProposal->custo_registo_automovel }}">
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" name="registo_pago" value="1" {{ $convertedProposal->registo_pago ? 'checked' : '' }}>
                    <label class="form-check-label">Pago</label>
                </div>
            </div>
        </div>

        <hr>

        {{-- TRANCHES --}}
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Valor Primeira Tranche</label>
                <input type="number" step="0.01" name="valor_primeira_tranche" class="form-control" value="{{ $convertedProposal->valor_primeira_tranche }}">
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" name="primeira_tranche_pago" value="1" {{ $convertedProposal->primeira_tranche_pago ? 'checked' : '' }}>
                    <label class="form-check-label">Pago</label>
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Valor Segunda Tranche</label>
                <input type="number" step="0.01" name="valor_segunda_tranche" class="form-control" value="{{ $convertedProposal->valor_segunda_tranche }}">
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" name="segunda_tranche_pago" value="1" {{ $convertedProposal->segunda_tranche_pago ? 'checked' : '' }}>
                    <label class="form-check-label">Pago</label>
                </div>
            </div>
        </div>

        <hr>

        {{-- CARRO + COMISSÃO --}}
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Valor Carro</label>
                <input type="number" step="0.01" name="valor_carro" class="form-control" value="{{ $convertedProposal->valor_carro }}">
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" name="carro_pago" value="1" {{ $convertedProposal->carro_pago ? 'checked' : '' }}>
                    <label class="form-check-label">Carro Pago</label>
                </div>
            </div>
            <div class="col-md-3">
                <label for="iva_dedutivel" class="form-label">IVA dedutível</label>
                <select name="iva_dedutivel" id="iva_dedutivel" class="form-control">
                    <option value="0" {{ !$convertedProposal->iva_dedutivel ? 'selected' : '' }}>Não</option>
                    <option value="1" {{ $convertedProposal->iva_dedutivel ? 'selected' : '' }}>Sim</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Valor Comissão</label>
                <input type="number" step="0.01" name="valor_comissao" class="form-control" value="{{ $convertedProposal->valor_comissao }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">Valor Comissão Final</label>
                <input type="number" step="0.01" name="valor_comissao_final" class="form-control" value="{{ $convertedProposal->valor_comissao_final }}">
            </div>
        </div>

        <hr>

        {{-- CONTACTOS + OBS --}}
        <div class="mb-3">
            <label class="form-label">Contactos Stand</label>
            <textarea name="contactos_stand" class="form-control" rows="2">{{ $convertedProposal->contactos_stand }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Observações</label>
            <textarea name="observacoes" class="form-control" rows="3">{{ $convertedProposal->observacoes }}</textarea>
        </div>
        <div class="mt-4 d-flex justify-content-between">
            <a href="{{ route('converted-proposals.index') }}" class="btn btn-danger px-4 py-2 rounded-pill shadow">Voltar</a>
            <button type="submit" class="btn btn-success px-4 py-2 rounded-pill shadow">
                <i class="bi bi-check-circle me-1"></i> Salvar
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const statusSelect = document.getElementById('status');
        const responseBox = document.getElementById('statusResponse');

        statusSelect.addEventListener('change', function() {
            const newStatus = this.value;
            const id = this.dataset.id;

            fetch(`/gestao/converted-proposals/${id}/update-status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        status: newStatus
                    })
                })
                .then(response => response.json()) // <- espera JSON
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                    }
                })
                .catch(err => alert("Ocorreu um erro ao atualizar o estado da proposta."));
        });
    });
</script>
@endpush