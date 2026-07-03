@extends('layouts.admin-v2')

@section('title', isset($movement) ? 'Editar Movimento' : 'Novo Movimento')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    .ts-wrapper { width: 100%; }
    .ts-wrapper .ts-control { border: 1px solid #dee2e6; border-radius: .375rem; min-height: 38px; }
    .ts-wrapper.focus .ts-control { border-color: #86b7fe; box-shadow: 0 0 0 .25rem rgba(13,110,253,.25); }
</style>
@endpush

@section('content')

@php
    $isEdit      = isset($movement);
    $actionLabel = $isEdit ? 'Editar' : 'Novo';
@endphp

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-journal-text', 'label' => 'Movimentos', 'href' => route('admin.v2.movements.index')],
        ['icon' => '', 'label' => $actionLabel],
    ],
    'title'       => $actionLabel . ' Movimento',
    'subtitle'    => '',
    'actionHref'  => '',
    'actionLabel' => '',
])

<form action="{{ $isEdit ? route('admin.v2.movements.update', $movement->id) : route('admin.v2.movements.store') }}"
      method="POST" enctype="multipart/form-data">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="row g-4">

        {{-- ── COLUNA PRINCIPAL ──────────────────────────────────────────── --}}
        <div class="col-lg-8">

            {{-- CLASSIFICAÇÃO --}}
            <div class="modern-card mb-4">
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-tag me-2"></i>Classificação</h5>
                </div>
                <div class="modern-card-body">
                    <div class="row g-3">

                        <div class="col-md-4">
                            <label class="form-label required fw-semibold">Tipo</label>
                            <select name="movement_type" id="movement_type"
                                    class="form-select @error('movement_type') is-invalid @enderror" required>
                                <option value="expense" {{ old('movement_type', $movement->movement_type ?? 'expense') === 'expense' ? 'selected' : '' }}>
                                    Despesa
                                </option>
                                <option value="income" {{ old('movement_type', $movement->movement_type ?? '') === 'income' ? 'selected' : '' }}>
                                    Receita
                                </option>
                            </select>
                            @error('movement_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Categoria</label>
                            <select name="category" id="category"
                                    class="form-select @error('category') is-invalid @enderror">
                                <option value="">Selecione uma categoria...</option>
                                @foreach(\App\Models\Expense::categories() as $val => $label)
                                    <option value="{{ $val }}"
                                        {{ old('category', $movement->category ?? '') === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Campo condicional: Legalização associada (só aparece quando categoria = legalization) --}}
                        <div class="col-12" id="legalization_field" style="display:none;">
                            <label class="form-label fw-semibold">
                                Legalização associada
                                <span class="text-muted fw-normal">(opcional — para legalizações sem importação)</span>
                            </label>
                            <select name="legalization_id" id="legalization_id"
                                    class="form-select @error('legalization_id') is-invalid @enderror">
                                <option value="">Sem legalização associada</option>
                                @foreach($legalizations as $leg)
                                    @php
                                        $legLabel = $leg->marca . ' ' . $leg->modelo;
                                        if ($leg->matricula) $legLabel .= ' — ' . $leg->matricula;
                                        if ($leg->client) $legLabel .= ' (' . $leg->client->name . ')';
                                    @endphp
                                    <option value="{{ $leg->id }}"
                                        {{ old('legalization_id', $movement->legalization_id ?? '') == $leg->id ? 'selected' : '' }}>
                                        #{{ $leg->id }} — {{ $legLabel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('legalization_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

            {{-- INFORMAÇÃO PRINCIPAL --}}
            <div class="modern-card mb-4">
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-info-circle me-2"></i>Informação</h5>
                </div>
                <div class="modern-card-body">
                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label required fw-semibold">Título / Descrição</label>
                            <input type="text" name="title"
                                   class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', $movement->title ?? '') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Veículo (Opcional)</label>
                            <select name="v3_vehicle_id"
                                    class="form-select @error('v3_vehicle_id') is-invalid @enderror">
                                <option value="">Sem veículo</option>
                                @foreach($vehicles as $v)
                                    <option value="{{ $v->id }}"
                                        {{ old('v3_vehicle_id', $movement->v3_vehicle_id ?? '') == $v->id ? 'selected' : '' }}>
                                        {{ $v->reference }} – {{ $v->brand }} {{ $v->model }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Cliente (Opcional)</label>
                            <select name="client_id"
                                    class="form-select @error('client_id') is-invalid @enderror">
                                <option value="">Sem cliente</option>
                                @foreach($clients as $c)
                                    <option value="{{ $c->id }}"
                                        {{ old('client_id', $movement->client_id ?? '') == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Parceiro / Fornecedor</label>
                            <select name="partner_id"
                                    class="form-select @error('partner_id') is-invalid @enderror">
                                <option value="">Sem parceiro</option>
                                @foreach($partners as $p)
                                    <option value="{{ $p->id }}"
                                        {{ old('partner_id', $movement->partner_id ?? '') == $p->id ? 'selected' : '' }}>
                                        {{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Data</label>
                            <input type="date" name="expense_date"
                                   class="form-control @error('expense_date') is-invalid @enderror"
                                   value="{{ old('expense_date', isset($movement) ? $movement->expense_date?->format('Y-m-d') : date('Y-m-d')) }}"
                                   required>
                            @error('expense_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Observações</label>
                            <textarea name="observations" rows="3"
                                      class="form-control @error('observations') is-invalid @enderror">{{ old('observations', $movement->observations ?? '') }}</textarea>
                        </div>

                    </div>
                </div>
            </div>

            {{-- VALORES --}}
            <div class="modern-card mb-4">
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-currency-euro me-2"></i>Valores</h5>
                </div>
                <div class="modern-card-body">
                    <div class="row g-3">

                        <div class="col-md-4">
                            <label class="form-label required fw-semibold">Montante Bruto (€)</label>
                            <input type="number" name="amount" id="amount"
                                   class="form-control @error('amount') is-invalid @enderror"
                                   value="{{ old('amount', $movement->amount ?? '') }}"
                                   step="0.01" min="0" required>
                            <small class="text-muted">Valor total com IVA incluído</small>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Taxa IVA (%)</label>
                            <select name="vat_rate" id="vat_rate" class="form-select @error('vat_rate') is-invalid @enderror">
                                <option value="0" {{ old('vat_rate', $movement->vat_rate ?? 23) == 0 ? 'selected' : '' }}>Sem IVA</option>
                                <option value="6"  {{ old('vat_rate', $movement->vat_rate ?? 23) == 6  ? 'selected' : '' }}>6%</option>
                                <option value="13" {{ old('vat_rate', $movement->vat_rate ?? 23) == 13 ? 'selected' : '' }}>13%</option>
                                <option value="19" {{ old('vat_rate', $movement->vat_rate ?? 23) == 19 ? 'selected' : '' }}>19%</option>
                                <option value="23" {{ old('vat_rate', $movement->vat_rate ?? 23) == 23 ? 'selected' : '' }}>23%</option>
                                <option value="25" {{ old('vat_rate', $movement->vat_rate ?? 23) == 25 ? 'selected' : '' }}>25%</option>
                            </select>
                        </div>

                        {{-- Live breakdown --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Resumo</label>
                            <div class="border rounded p-3 bg-light">
                                <div class="d-flex justify-content-between small">
                                    <span class="text-muted">Líquido (s/ IVA)</span>
                                    <strong id="preview_net">€ 0,00</strong>
                                </div>
                                <div class="d-flex justify-content-between small mt-1">
                                    <span class="text-muted">IVA</span>
                                    <strong id="preview_vat" class="text-warning">€ 0,00</strong>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between small fw-bold">
                                    <span>Total Bruto</span>
                                    <strong id="preview_gross" class="text-primary">€ 0,00</strong>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ANEXO --}}
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-paperclip me-2"></i>Comprovativo</h5>
                </div>
                <div class="modern-card-body">
                    @if($isEdit && $movement->attachment_path)
                        <div class="mb-3 d-flex align-items-center gap-3">
                            @php
                                $ext     = strtolower(pathinfo($movement->attachment_path, PATHINFO_EXTENSION));
                                $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                            @endphp
                            @if($isImage)
                                <a href="{{ asset('storage/' . $movement->attachment_path) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $movement->attachment_path) }}" alt="Anexo"
                                         style="max-height:80px;border-radius:6px;border:1px solid #ddd;">
                                </a>
                            @else
                                <a href="{{ asset('storage/' . $movement->attachment_path) }}" target="_blank"
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-file-earmark-pdf"></i> Ver ficheiro
                                </a>
                            @endif
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="remove_attachment" value="1" id="remove_attachment">
                                <label class="form-check-label text-danger" for="remove_attachment">
                                    Remover anexo
                                </label>
                            </div>
                        </div>
                    @endif
                    <input type="file" name="attachment" id="attachment"
                           class="form-control @error('attachment') is-invalid @enderror"
                           accept=".jpg,.jpeg,.png,.gif,.webp,.pdf">
                    <small class="text-muted">JPG, PNG, GIF, WEBP, PDF – máx. 10 MB</small>
                    @error('attachment')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div id="attachmentPreview" class="mt-2" style="display:none;">
                        <img id="attachmentPreviewImg" src="" alt="Preview"
                             style="max-height:120px;border-radius:6px;border:1px solid #ddd;">
                    </div>
                </div>
            </div>

        </div>

        {{-- ── COLUNA LATERAL ────────────────────────────────────────────── --}}
        <div class="col-lg-4">

            {{-- AÇÕES --}}
            @include('components.admin.action-card', [
                'cancelButtonHref'   => route('admin.v2.movements.index'),
                'submitButtonLabel'  => $isEdit ? 'Guardar Alterações' : 'Criar Movimento',
                'timestamps'         => $isEdit ? ['created_at' => $movement->created_at, 'updated_at' => $movement->updated_at] : null,
            ])

            {{-- PAGAMENTO --}}
            <div class="modern-card mt-4">
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-credit-card me-2"></i>Pagamento</h5>
                </div>
                <div class="modern-card-body">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Método de Pagamento</label>
                        <select name="payment_method" class="form-select">
                            <option value="">Não especificado</option>
                            @foreach(\App\Models\Expense::paymentMethods() as $val => $label)
                                <option value="{{ $val }}"
                                    {{ old('payment_method', $movement->payment_method ?? '') === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label fw-semibold">Estado do Pagamento</label>
                        <select name="status" class="form-select">
                            @foreach(\App\Models\Expense::statuses() as $val => $label)
                                <option value="{{ $val }}"
                                    {{ old('status', $movement->status ?? 'paid') === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>
            </div>

        </div>
    </div>
</form>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2/dist/js/tom-select.complete.min.js"></script>
<script>
(function () {
    const amountInput  = document.getElementById('amount');
    const vatSelect    = document.getElementById('vat_rate');
    const previewNet   = document.getElementById('preview_net');
    const previewVat   = document.getElementById('preview_vat');
    const previewGross = document.getElementById('preview_gross');

    function fmt(n) {
        return '€ ' + n.toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function recalc() {
        const gross   = parseFloat(amountInput.value) || 0;
        const vatRate = parseFloat(vatSelect.value)   || 0;
        const net     = vatRate > 0 ? gross / (1 + vatRate / 100) : gross;
        const vat     = gross - net;

        previewNet.textContent   = fmt(net);
        previewVat.textContent   = fmt(vat);
        previewGross.textContent = fmt(gross);
    }

    amountInput.addEventListener('input',  recalc);
    vatSelect.addEventListener('change', recalc);
    recalc();

    // Mostrar/esconder campo de legalização consoante categoria
    // (usamos a instância TomSelect para limpar o valor quando se esconde)
    const categorySelect    = document.getElementById('category');
    const legalizationField = document.getElementById('legalization_field');
    let tsLegal             = null; // instância criada depois do Tom Select estar pronto

    function toggleLegalizationField() {
        const show = categorySelect.value === 'legalization';
        legalizationField.style.display = show ? '' : 'none';
        if (!show && tsLegal) tsLegal.clear();
    }

    categorySelect.addEventListener('change', toggleLegalizationField);
    toggleLegalizationField();

    // Tom Select — selects com pesquisa
    const tsOpts = { plugins: ['dropdown_input'], maxOptions: 300, searchField: ['text'] };

    new TomSelect('#v3_vehicle_id', { ...tsOpts, placeholder: 'Pesquisar veículo...' });
    new TomSelect('#client_id',     { ...tsOpts, placeholder: 'Pesquisar cliente...' });
    new TomSelect('#partner_id',    { ...tsOpts, placeholder: 'Pesquisar parceiro...' });
    tsLegal = new TomSelect('#legalization_id', { ...tsOpts, placeholder: 'Pesquisar legalização...' });

    // Image preview
    document.getElementById('attachment').addEventListener('change', function () {
        const preview = document.getElementById('attachmentPreview');
        const img     = document.getElementById('attachmentPreviewImg');
        if (this.files && this.files[0] && this.files[0].type.startsWith('image/')) {
            img.src = URL.createObjectURL(this.files[0]);
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    });
})();
</script>
@endpush
