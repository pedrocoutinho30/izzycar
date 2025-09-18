<!-- resources/views/proposals/form.blade.php -->
@extends('layouts.admin')

@section('main-content')

<div class="container-fluid px-4 py-5">
    <h2 class="mb-4 fw-bold text-primary">
        Ver Formulário Proposta
    </h2>

    <div class="bg-white p-4 rounded shadow-sm">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5 class="mb-3">Detalhes do Cliente</h5>
                <p><strong>Nome:</strong> <a href="{{ route('clients.edit', $form->client_id) }}">{{ $form->name }}</a></p>
                <p><strong>Email:</strong> {{ $form->email }}</p>
                <p><strong>Telefone:</strong> {{ $form->phone }}</p>
                <p><strong>Mensagem:</strong> {{ $form->message }}</p>
                <p><strong>Origem:</strong> {{ $form->source }}</p>
            </div>
            <div class="col-md-4 mb-4">
                <h5 class="mb-3">Detalhes da Proposta</h5>

                @if($form->ad_links != null && $form->ad_option == 'sim')
                <p> <strong>Link:</strong> {{ $form->ad_links }}</p>
                @endif
                @if($form->ad_option == 'nao_sei')
                <p><strong>Marca:</strong> {{ $form->brand }}</p>
                <p><strong>Modelo:</strong> {{ $form->model }}</p>
                <p><strong>Versão:</strong> {{ $form->version }}</p>
                <p><strong>Combustível:</strong> {{ $form->fuel }}</p>
                <p><strong>Cor:</strong> {{ $form->color }}</p>
                <p><strong>Ano:</strong> {{ $form->year_min }}</p>
                <p><strong>Kms:</strong> {{ $form->kms_max }}</p>
                <p><strong>Caixa:</strong> {{ $form->gearbox }}</p>
                <p><strong>Orçamento:</strong> {{ $form->budget }}</p>
                <p><strong>Extras:</strong> {{ $form->extras }}</p>
                @endif
            </div>
            <div class="col-md-4 mb-4">
                <h5 class="mb-3">Estado</h5>
                <select name="status" id="status" class="form-control rounded shadow-sm">
                    <option value="novo" {{ $form->status == 'novo' ? 'selected' : '' }}>Novo</option>
                    <option value="em_andamento" {{ $form->status == 'em_andamento' ? 'selected' : '' }}>Em Andamento</option>
                    <option value="concluido" {{ $form->status == 'concluido' ? 'selected' : '' }}>Concluído</option>
                    <option value="cancelado" {{ $form->status == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                </select>
                @if($form->proposal_id != null)
                <p><strong>ID da Proposta:</strong> <a href="{{ route('proposals.edit', $form->proposal_id) }}">{{ $form->proposal_id }}</a></p>
                @endif
            </div>

        </div>
    </div>
</div>

    @endsection

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const statusSelect = document.getElementById("status");

            // Converte a variável PHP $form em JSON para usar em JS
            const formData = @json($form);

            statusSelect.addEventListener("change", function() {
                if (this.value === "em_andamento" && !formData.ad_option == 'nao_sei' && formData.proposal_id == null) {
                    fetch("{{ route('proposals.create_by_form') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                form: formData
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log("Resposta do servidor:", data);
                        })
                        .catch(error => {
                            console.error("Erro:", error);
                        });
                }
            });
        });
    </script>