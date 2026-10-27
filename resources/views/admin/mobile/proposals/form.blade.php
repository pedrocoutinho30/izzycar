@extends('layouts.admin-mobile')

@php
    $editar = isset($proposal->id);
    $aceite = $accepted ?? false;
    $voltar = match (true) {
        $editar => route('admin.mobile.proposals.show', $proposal->id),
        $importOpportunity !== null => route('admin.mobile.form-proposals.opportunities.show', [$importOpportunity->form_proposal_id, $importOpportunity->id]),
        $formProposal !== null => route('admin.mobile.form-proposals.show', $formProposal->id),
        (bool) $client->is_lead => route('admin.mobile.leads.show', $client->id),
        default => route('admin.mobile.clients.show', $client->id),
    };
    $valor = fn (string $campo) => old($campo, $proposal->{$campo} ?? null);
    // Dinheiro com a vírgula como separador decimal ("1,500", "20000,00"): nunca fica no
    // formato "N.NNN", que o DecimalInput leria como milhares.
    $dinheiro = function (string $campo) use ($valor) {
        $v = $valor($campo);

        return is_numeric($v) ? str_replace('.', ',', (string) $v) : $v;
    };
    $links = old('other_links', json_decode((string) ($proposal->other_links ?? ''), true) ?: []);
    $links = array_pad(array_slice(array_values((array) $links), 0, 5), 3, null);
    $custos = [
        'transport_cost' => ['Transporte (€)', true],
        'inspection_commission_cost' => ['Inspeção na origem (€)', false],
        'ipo_cost' => ['IPO (€)', false],
        'imt_cost' => ['IMT (€)', false],
        'registration_cost' => ['Registo automóvel (€)', false],
        'license_plate_cost' => ['Matrícula (€)', false],
        'isv_cost' => ['ISV (€)', false],
        'commission_cost' => ['Comissão (€)', false],
        'iuc_cost' => ['IUC (€, à parte)', false],
    ];
    // Abre a secção com erros (por omissão, a primeira).
    $seccaoComErro = collect([
        'custos' => array_keys($custos),
        'notas' => ['url', 'notes', 'other_links', 'proposed_car_features'],
        'estado' => ['status'],
    ])->search(fn ($campos) => collect($campos)->contains(fn ($c) => $errors->has($c))) ?: 'viatura';
@endphp

@section('title', $editar ? 'Editar cotação' : 'Nova cotação')

@section('header_actions')
    <a href="{{ $voltar }}" class="order-first m-topbar-back" aria-label="Voltar"><i class="bi bi-chevron-left fs-4"></i></a>
@endsection

@section('content')
    <form action="{{ $editar ? route('admin.mobile.proposals.update', $proposal->id) : route('admin.mobile.proposals.store') }}"
          method="POST" enctype="multipart/form-data">
        @csrf
        @if($editar) @method('PUT') @endif
        <input type="hidden" name="client_id" value="{{ $client->id }}">
        @if($formProposal)
            <input type="hidden" name="form_proposal_id" value="{{ $formProposal->id }}">
        @endif
        @if($importOpportunity)
            <input type="hidden" name="import_opportunity_id" value="{{ $importOpportunity->id }}">
        @endif

        <div class="m-card">
            <div class="fw-semibold">{{ $client->name }}</div>
            <div class="small text-muted">
                {{ $client->is_lead ? 'Lead' : 'Cliente' }}
                @if($importOpportunity) · a partir da oportunidade {{ $importOpportunity->title }}
                @elseif($formProposal) · a partir do pedido {{ $formProposal->label }}
                @endif
            </div>
            @if($catalogWarning)
                <div class="alert alert-warning small py-2 mt-2 mb-0">{{ $catalogWarning }}</div>
            @endif
        </div>

        <div class="accordion m-accordion" id="proposalForm">
            {{-- 1. Viatura --}}
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button {{ $seccaoComErro === 'viatura' ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#f-viatura" aria-expanded="{{ $seccaoComErro === 'viatura' ? 'true' : 'false' }}" aria-controls="f-viatura">
                        <i class="bi bi-car-front me-2"></i> 1. Viatura
                    </button>
                </h2>
                <div id="f-viatura" class="accordion-collapse collapse {{ $seccaoComErro === 'viatura' ? 'show' : '' }}">
                    <div class="accordion-body d-grid gap-3">
                        @include('admin.mobile.form-proposals._brand-model', [
                            'brand' => $valor('brand'),
                            'model' => $valor('model'),
                            'required' => true,
                            'idPrefix' => 'mp',
                        ])
                        <div>
                            <label class="form-label fw-semibold" for="mp-version">Versão</label>
                            <input type="text" id="mp-version" name="version" value="{{ $valor('version') }}" class="form-control" maxlength="255" placeholder="Ex.: 320d M Sport">
                        </div>
                        <div>
                            <label class="form-label fw-semibold" for="mp-year">Ano</label>
                            <input type="text" id="mp-year" name="proposed_car_year_month" value="{{ $valor('proposed_car_year_month') }}" class="form-control" inputmode="numeric" placeholder="{{ date('Y') }}">
                        </div>
                        <div>
                            <label class="form-label fw-semibold" for="mp-km">Quilómetros</label>
                            <input type="number" id="mp-km" name="proposed_car_mileage" value="{{ $valor('proposed_car_mileage') }}" class="form-control @error('proposed_car_mileage') is-invalid @enderror" min="0" step="1" inputmode="numeric">
                        </div>
                        <div>
                            <label class="form-label fw-semibold" for="mp-fuel">Combustível</label>
                            <select id="mp-fuel" name="fuel" class="form-select @error('fuel') is-invalid @enderror">
                                <option value="">—</option>
                                @foreach(\App\Services\ProposalService::FUELS as $combustivel)
                                    <option value="{{ $combustivel }}" @selected($valor('fuel') === $combustivel)>{{ $combustivel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold" for="mp-value">Valor do carro (€)</label>
                            <input type="text" id="mp-value" name="proposed_car_value" value="{{ $dinheiro('proposed_car_value') }}" class="form-control @error('proposed_car_value') is-invalid @enderror" inputmode="decimal" autocomplete="off">
                        </div>
                        <div>
                            <label class="form-label fw-semibold" for="mp-image">Foto</label>
                            <input type="file" id="mp-image" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/jpeg,image/png,image/webp,image/avif">
                            @if($editar && $proposal->images)
                                <div class="form-text">Já tem foto: uma nova substitui-a.</div>
                            @elseif($importOpportunity?->photo_path)
                                <div class="form-text">Sem foto, usa a da oportunidade.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Custos --}}
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button {{ $seccaoComErro === 'custos' ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#f-custos" aria-expanded="{{ $seccaoComErro === 'custos' ? 'true' : 'false' }}" aria-controls="f-custos">
                        <i class="bi bi-currency-euro me-2"></i> 2. Custos
                    </button>
                </h2>
                <div id="f-custos" class="accordion-collapse collapse {{ $seccaoComErro === 'custos' ? 'show' : '' }}">
                    <div class="accordion-body d-grid gap-3">
                        @foreach($custos as $campo => [$rotulo, $obrigatorio])
                            <div>
                                <label class="form-label fw-semibold" for="mp-{{ $campo }}">{{ $rotulo }} @if($obrigatorio)<span class="text-danger">*</span>@endif</label>
                                <input type="text" id="mp-{{ $campo }}" name="{{ $campo }}" value="{{ $dinheiro($campo) }}"
                                       class="form-control @error($campo) is-invalid @enderror" inputmode="decimal" autocomplete="off" @required($obrigatorio)>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 3. Notas e links --}}
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button {{ $seccaoComErro === 'notas' ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#f-notas" aria-expanded="{{ $seccaoComErro === 'notas' ? 'true' : 'false' }}" aria-controls="f-notas">
                        <i class="bi bi-journal-text me-2"></i> 3. Notas e links
                    </button>
                </h2>
                <div id="f-notas" class="accordion-collapse collapse {{ $seccaoComErro === 'notas' ? 'show' : '' }}">
                    <div class="accordion-body d-grid gap-3">
                        <div>
                            <label class="form-label fw-semibold" for="mp-url">URL do anúncio</label>
                            <input type="url" id="mp-url" name="url" value="{{ $valor('url') }}" class="form-control @error('url') is-invalid @enderror" inputmode="url" autocapitalize="none" placeholder="https://...">
                        </div>
                        <div>
                            <span class="form-label fw-semibold d-block">Outros links</span>
                            <div class="d-grid gap-2">
                                @foreach($links as $i => $link)
                                    <input type="url" name="other_links[]" value="{{ $link }}" class="form-control" inputmode="url" autocapitalize="none" placeholder="https://..." aria-label="Outro link {{ $i + 1 }}">
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <label class="form-label fw-semibold" for="mp-features">Equipamento e extras</label>
                            <textarea id="mp-features" name="proposed_car_features" rows="3" class="form-control" placeholder="Ex.: Apple CarPlay, câmara traseira, teto de abrir...">{{ $valor('proposed_car_features') }}</textarea>
                        </div>
                        <div>
                            <label class="form-label fw-semibold" for="mp-notes">Notas internas</label>
                            <textarea id="mp-notes" name="notes" rows="4" class="form-control" placeholder="Não aparecem na página do cliente.">{{ $valor('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. Estado --}}
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button {{ $seccaoComErro === 'estado' ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#f-estado" aria-expanded="{{ $seccaoComErro === 'estado' ? 'true' : 'false' }}" aria-controls="f-estado">
                        <i class="bi bi-circle-half me-2"></i> 4. Estado
                    </button>
                </h2>
                <div id="f-estado" class="accordion-collapse collapse {{ $seccaoComErro === 'estado' ? 'show' : '' }}">
                    <div class="accordion-body d-grid gap-2">
                        @if($aceite)
                            <input type="hidden" name="status" value="Aprovada">
                            <div class="small text-muted">Cotação aceite: o estado fica "Aprovada" — para a anular, cancele a cotação convertida.</div>
                        @else
                            <label class="form-label fw-semibold" for="mp-status">Estado</label>
                            <select id="mp-status" name="status" class="form-select @error('status') is-invalid @enderror">
                                @foreach($statuses as $opcao)
                                    @continue($opcao === 'Aprovada')
                                    <option value="{{ $opcao }}" @selected(($valor('status') ?? 'Pendente') === $opcao)>{{ $opcao }}</option>
                                @endforeach
                            </select>
                            <div class="small text-muted">"Enviado" envia o email com o link ao cliente. Para aceitar a cotação, use o botão "Aceitar" na ficha.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg fw-semibold w-100 mt-3">{{ $editar ? 'Guardar cotação' : 'Criar cotação' }}</button>
    </form>
@endsection

@push('scripts')
<script>
    // Um campo inválido numa secção fechada abre-a, para o browser o poder mostrar.
    document.querySelectorAll('#proposalForm input, #proposalForm select, #proposalForm textarea').forEach((campo) => {
        campo.addEventListener('invalid', () => {
            const seccao = campo.closest('.accordion-collapse');
            if (seccao && !seccao.classList.contains('show')) {
                seccao.classList.add('show');
                seccao.previousElementSibling?.querySelector('.accordion-button')?.classList.remove('collapsed');
            }
        });
    });
</script>
@endpush
