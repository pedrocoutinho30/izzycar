@extends('layouts.admin-mobile')

@php
    $editar = $movement->exists;
    $valor = fn (string $campo) => old($campo, $movement->{$campo});
    // Dinheiro com a vírgula como separador decimal ("1200,5"): nunca fica no
    // formato "N.NNN", que o DecimalInput leria como milhares.
    $dinheiro = function (string $campo) use ($valor) {
        $v = $valor($campo);

        return is_numeric($v) ? str_replace('.', ',', (string) (float) $v) : $v;
    };
    $data = old('expense_date', $movement->expense_date?->format('Y-m-d'));
    // Taxas de IVA do formulário do desktop. Num movimento novo, 23% por omissão (já vem no modelo);
    // num antigo sem taxa fica "Não definido" e uma taxa fora da lista mantém-se como "(atual)".
    $iva = old('vat_rate', $movement->vat_rate);
    $taxas = [0 => 'Sem IVA', 6 => '6%', 13 => '13%', 19 => '19%', 23 => '23%', 25 => '25%'];
    $ivaForaDaLista = is_numeric($iva) && ! in_array((float) $iva, array_map('floatval', array_keys($taxas)), true);
    // Com vírgula decimal, como o resto do formulário (o DecimalInput lê "21.5" como milhares).
    $ivaTexto = is_numeric($iva) ? str_replace('.', ',', (string) (float) $iva) : '';
    // Valor atual fora da lista de opções: acrescenta-se como opção selecionada, para não se perder ao guardar.
    $foraDaLista = function (string $campo, array $lista) use ($valor) {
        $v = $valor($campo);

        return is_string($v) && $v !== '' && ! array_key_exists($v, $lista) ? $v : null;
    };
    $voltar = $editar ? route('admin.mobile.movements.show', $movement->id) : route('admin.mobile.movements.index');
@endphp

@section('title', $editar ? 'Editar movimento' : 'Novo movimento')

@section('header_actions')
    <a href="{{ $voltar }}" class="order-first m-topbar-back" aria-label="Voltar"><i class="bi bi-chevron-left fs-4"></i></a>
@endsection

@section('content')
    <form action="{{ $editar ? route('admin.mobile.movements.update', $movement->id) : route('admin.mobile.movements.store') }}" method="POST" class="d-grid gap-3" style="font-size:16px">
        @csrf
        @if($editar) @method('PUT') @endif

        {{-- Classificação --}}
        <div class="m-card d-grid gap-3 mb-0">
            <div class="fw-semibold"><i class="bi bi-tag me-1"></i> Classificação</div>

            <div>
                <label for="mov-type" class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                <select id="mov-type" name="movement_type" class="form-select @error('movement_type') is-invalid @enderror" required>
                    @foreach(\App\Models\Expense::movementTypes() as $opcao => $nome)
                        <option value="{{ $opcao }}" @selected($valor('movement_type') === $opcao)>{{ $nome }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="mov-category" class="form-label fw-semibold">Categoria</label>
                <select id="mov-category" name="category" class="form-select @error('category') is-invalid @enderror">
                    <option value="">Selecione uma categoria...</option>
                    @foreach(\App\Models\Expense::categories() as $opcao => $nome)
                        <option value="{{ $opcao }}" @selected($valor('category') === $opcao)>{{ $nome }}</option>
                    @endforeach
                    @if($extra = $foraDaLista('category', \App\Models\Expense::categories()))
                        <option value="{{ $extra }}" selected>{{ $extra }} (atual)</option>
                    @endif
                </select>
            </div>

            {{-- Como no desktop: só aparece com a categoria Legalização (sem JS, fica sempre visível). --}}
            <div id="mov-legalization-field">
                <label for="mov-legalization" class="form-label fw-semibold">Legalização associada</label>
                <select id="mov-legalization" name="legalization_id" class="form-select @error('legalization_id') is-invalid @enderror">
                    <option value="">Sem legalização associada</option>
                    @foreach($legalizations as $leg)
                        <option value="{{ $leg->id }}" @selected((string) $valor('legalization_id') === (string) $leg->id)>
                            #{{ $leg->id }} — {{ $leg->marca }} {{ $leg->modelo }}@if($leg->matricula) — {{ $leg->matricula }}@endif @if($leg->client)({{ $leg->client->name }})@endif
                        </option>
                    @endforeach
                </select>
                <div class="form-text">Opcional — para legalizações sem importação.</div>
            </div>
        </div>

        {{-- Informação e valores --}}
        <div class="m-card d-grid gap-3 mb-0">
            <div class="fw-semibold"><i class="bi bi-currency-euro me-1"></i> Valores</div>

            <div>
                <label for="mov-title" class="form-label fw-semibold">Título / Descrição <span class="text-danger">*</span></label>
                <input type="text" id="mov-title" name="title" value="{{ $valor('title') }}" class="form-control @error('title') is-invalid @enderror"
                       required maxlength="255" enterkeyhint="next">
            </div>

            <div>
                <label for="mov-amount" class="form-label fw-semibold">Montante bruto (€) <span class="text-danger">*</span></label>
                <input type="text" id="mov-amount" name="amount" value="{{ $dinheiro('amount') }}" class="form-control @error('amount') is-invalid @enderror"
                       required inputmode="decimal" autocomplete="off" placeholder="0,00" enterkeyhint="next">
                <div class="form-text">Valor total com IVA incluído.</div>
            </div>

            <div>
                <label for="mov-vat" class="form-label fw-semibold">Taxa de IVA</label>
                <select id="mov-vat" name="vat_rate" class="form-select @error('vat_rate') is-invalid @enderror">
                    @if($iva === null || $iva === '')
                        <option value="" selected>Não definido</option>
                    @endif
                    @foreach($taxas as $taxa => $nome)
                        <option value="{{ $taxa }}" @selected($iva !== null && $iva !== '' && (float) $iva == $taxa)>{{ $nome }}</option>
                    @endforeach
                    @if($ivaForaDaLista)
                        <option value="{{ $ivaTexto }}" selected>{{ $ivaTexto }}% (atual)</option>
                    @endif
                </select>
            </div>

            <div>
                <label for="mov-date" class="form-label fw-semibold">Data <span class="text-danger">*</span></label>
                <input type="date" id="mov-date" name="expense_date" value="{{ $data }}" class="form-control @error('expense_date') is-invalid @enderror" required>
            </div>

            <div>
                <label for="mov-status" class="form-label fw-semibold">Estado do pagamento</label>
                <select id="mov-status" name="status" class="form-select @error('status') is-invalid @enderror">
                    @foreach(\App\Models\Expense::statuses() as $opcao => $nome)
                        <option value="{{ $opcao }}" @selected(($valor('status') ?? 'paid') === $opcao)>{{ $nome }}</option>
                    @endforeach
                    @if($extra = $foraDaLista('status', \App\Models\Expense::statuses()))
                        <option value="{{ $extra }}" selected>{{ $extra }} (atual)</option>
                    @endif
                </select>
            </div>

            <div>
                <label for="mov-payment" class="form-label fw-semibold">Método de pagamento</label>
                <select id="mov-payment" name="payment_method" class="form-select @error('payment_method') is-invalid @enderror">
                    <option value="">Não especificado</option>
                    @foreach(\App\Models\Expense::paymentMethods() as $opcao => $nome)
                        <option value="{{ $opcao }}" @selected($valor('payment_method') === $opcao)>{{ $nome }}</option>
                    @endforeach
                    @if($extra = $foraDaLista('payment_method', \App\Models\Expense::paymentMethods()))
                        <option value="{{ $extra }}" selected>{{ $extra }} (atual)</option>
                    @endif
                </select>
            </div>
        </div>

        {{-- Associações (as mesmas do desktop) --}}
        <div class="m-card d-grid gap-3 mb-0">
            <div class="fw-semibold"><i class="bi bi-link-45deg me-1"></i> Associar a</div>

            <div>
                <label for="mov-vehicle" class="form-label fw-semibold">Viatura</label>
                <select id="mov-vehicle" name="v3_vehicle_id" class="form-select @error('v3_vehicle_id') is-invalid @enderror">
                    <option value="">Sem viatura</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v->id }}" @selected((string) $valor('v3_vehicle_id') === (string) $v->id)>{{ $v->reference }} – {{ $v->brand }} {{ $v->model }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="mov-client" class="form-label fw-semibold">Cliente</label>
                <select id="mov-client" name="client_id" class="form-select @error('client_id') is-invalid @enderror">
                    <option value="">Sem cliente</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" @selected((string) $valor('client_id') === (string) $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="mov-partner" class="form-label fw-semibold">Parceiro / Fornecedor</label>
                <select id="mov-partner" name="partner_id" class="form-select @error('partner_id') is-invalid @enderror">
                    <option value="">Sem parceiro</option>
                    @foreach($partners as $p)
                        <option value="{{ $p->id }}" @selected((string) $valor('partner_id') === (string) $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="mov-observations" class="form-label fw-semibold">Observações</label>
                <textarea id="mov-observations" name="observations" rows="3" class="form-control @error('observations') is-invalid @enderror">{{ $valor('observations') }}</textarea>
            </div>

            <div class="small text-muted"><i class="bi bi-paperclip me-1"></i>O comprovativo (anexo) envia-se no desktop.</div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg fw-semibold">{{ $editar ? 'Guardar alterações' : 'Criar movimento' }}</button>
    </form>
@endsection

@push('scripts')
<script>
    // Como no desktop: a legalização só aparece com a categoria "Legalização" e limpa-se ao esconder.
    (function () {
        const categoria = document.getElementById('mov-category');
        const campo = document.getElementById('mov-legalization-field');
        const legalizacao = document.getElementById('mov-legalization');
        function alternar() {
            const mostrar = categoria.value === 'legalization';
            campo.hidden = !mostrar;
            if (!mostrar) legalizacao.value = '';
        }
        categoria.addEventListener('change', alternar);
        alternar();
    })();
</script>
@endpush
