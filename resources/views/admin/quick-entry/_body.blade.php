@php
    $card = $mobile ? 'm-card' : 'card card-body mb-3';
    $fieldLabels = [
        'movement_type' => 'Tipo', 'category' => 'Categoria', 'amount' => 'Valor', 'expense_date' => 'Data',
        'status' => 'Estado', 'payment_method' => 'Método de pagamento', 'client_id' => 'Cliente',
        'legalization_id' => 'Legalização', 'title' => 'Título', 'observations' => 'Observações', 'vat_rate' => 'IVA',
        'name' => 'Nome', 'phone' => 'Telefone', 'email' => 'Email', 'vat_number' => 'NIF', 'language' => 'Idioma', 'observation' => 'Observações',
    ];
    $sourceLabels = ['extracted' => 'Extraído da frase', 'inferred' => 'Inferido', 'default' => 'Valor por defeito', 'relation' => 'Preenchido por relação'];
    $sourceBadge = ['extracted' => 'bg-success', 'inferred' => 'bg-info text-dark', 'default' => 'bg-secondary', 'relation' => 'bg-primary'];
    $fields = $proposal['fields'] ?? [];
    $unresolved = $proposal['unresolved'] ?? [];
    $isReady = ($proposal['status'] ?? null) === 'ready';
    $pending = $interpretation && $interpretation->status === \App\Models\QuickEntryInterpretation::INTERPRETED;
    $initial = fn (string $f) => $fields[$f]['value'] ?? '';
    $current = fn (string $f) => old($f, $initial($f));
    $mark = fn (string $f) => in_array($f, $unresolved, true) ? 'border-warning border-2' : '';
    $none = \App\Services\QuickEntryService::NONE;
    $amountText = fn ($v) => is_numeric($v) ? str_replace('.', ',', (string) $v) : $v;
@endphp

<div class="{{ $card }}" style="font-size:16px">
    <form method="POST" action="{{ route($prefix . 'interpret') }}" id="qe-interpret" class="d-grid gap-3">
        @csrf
        <div>
            <label for="qe-text" class="form-label fw-semibold">Frase</label>
            <textarea id="qe-text" name="text" rows="3" maxlength="1000" class="form-control @error('text') is-invalid @enderror"
                      placeholder="Ex.: Criar movimento despesa categoria outros 10€ legalização fabio oliveira pago em dinheiro" required>{{ old('text', $interpretation?->original_text) }}</textarea>
            @error('text')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Pode escrever ou usar o ditado do teclado do telemóvel. A aplicação recebe apenas texto.</div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="submit" class="btn btn-primary"><i class="bi bi-lightning-charge me-1"></i> Interpretar</button>
        </div>
    </form>

    <details class="mt-3" @if(! $interpretation) open @endif>
        <summary class="fw-semibold">Exemplos de frases</summary>
        <ul class="list-unstyled mt-2 mb-1 d-grid gap-2">
            @foreach($examples as $example)
                <li><button type="button" class="btn btn-sm btn-outline-secondary text-start qe-example" data-text="{{ $example }}">{{ $example }}</button></li>
            @endforeach
        </ul>
        <div class="form-text">Comandos: «criar movimento» e «criar cliente» (disponíveis); «editar cliente» é reconhecido mas ainda não está disponível.</div>
    </details>
</div>

@if($interpretation)
    @if(! $isReady)
        <div class="alert {{ ($proposal['status'] ?? '') === 'not_implemented' ? 'alert-info' : 'alert-warning' }}">
            <i class="bi bi-info-circle me-1"></i> {{ $proposal['message'] }}
            @if(($proposal['status'] ?? '') === 'not_implemented')
                <div class="small mt-1">Comando reconhecido: <strong>{{ $proposal['command_label'] }}</strong>. Nada foi criado nem alterado.</div>
            @endif
        </div>
    @else
        @if(! $pending)
            <div class="alert alert-secondary">
                Esta proposta já foi <strong>{{ $interpretation->status }}</strong>@if($interpretation->confirmed_at) em {{ $interpretation->confirmed_at->format('d/m/Y H:i') }}@endif. Interprete a frase de novo para criar outra.
            </div>
        @endif

        @if($errors->any() && $pending)
            <div class="alert alert-danger">
                <strong>Não foi possível confirmar:</strong>
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- Resumo: extraído, inferido, por defeito e dúvidas, em separado --}}
        <div class="{{ $card }}">
            <div class="fw-semibold mb-2"><i class="bi bi-list-check me-1"></i> {{ $proposal['command_label'] }} — o que foi interpretado</div>
            <div class="row g-3">
                @foreach(['extracted', 'inferred', 'relation', 'default'] as $source)
                    @php $group = collect($fields)->filter(fn ($f, $k) => $f['source'] === $source && ($f['value'] !== null && $f['value'] !== '') && ! in_array($k, ['observations'], true)); @endphp
                    @if($group->isNotEmpty())
                        <div class="col-12 col-md-6">
                            <div class="small text-uppercase text-muted fw-semibold mb-1">{{ $sourceLabels[$source] }}</div>
                            <ul class="list-unstyled mb-0">
                                @foreach($group as $key => $f)
                                    <li><span class="text-muted">{{ $fieldLabels[$key] ?? $key }}:</span> <strong>{{ $f['label'] ?? $f['value'] }}</strong>
                                        @if($f['note'])<div class="small text-muted">{{ $f['note'] }}</div>@endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endforeach
                @if(! empty($proposal['warnings']) || $unresolved)
                    <div class="col-12">
                        <div class="small text-uppercase text-muted fw-semibold mb-1">Dúvidas e avisos</div>
                        <ul class="list-unstyled mb-0">
                            @foreach($proposal['warnings'] as $w)
                                <li class="{{ $w['level'] === 'error' ? 'text-danger' : ($w['level'] === 'info' ? 'text-muted' : 'text-warning-emphasis') }}">
                                    <i class="bi {{ $w['level'] === 'error' ? 'bi-x-octagon' : ($w['level'] === 'info' ? 'bi-info-circle' : 'bi-exclamation-triangle') }} me-1"></i>{{ $w['message'] }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

@if(($proposal['module'] ?? null) === 'cliente')
            @include('admin.quick-entry._client_form')
        @else
        {{-- Proposta editável --}}
        <form method="POST" action="{{ route($prefix . 'confirm', $interpretation->id) }}" id="qe-confirm" class="{{ $card }} d-grid gap-3" @if(! $pending) inert @endif>
            @csrf
            <div class="fw-semibold"><i class="bi bi-pencil-square me-1"></i> Proposta (editável)</div>

            @php
                $render = function (string $f) use ($fields, $sourceLabels, $sourceBadge, $fieldLabels) {
                    $s = $fields[$f]['source'] ?? null;
                    return $s ? '<span class="badge ' . $sourceBadge[$s] . ' ms-1">' . e($sourceLabels[$s]) . '</span>' : '';
                };
            @endphp

            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold" for="qe-type">Tipo <span class="text-danger">*</span>{!! $render('movement_type') !!}</label>
                    <select id="qe-type" name="movement_type" class="form-select {{ $mark('movement_type') }} @error('movement_type') is-invalid @enderror" required>
                        <option value="">Escolha…</option>
                        @foreach(\App\Models\Expense::movementTypes() as $v => $l)
                            <option value="{{ $v }}" @selected($current('movement_type') === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold" for="qe-amount">Valor (€) <span class="text-danger">*</span>{!! $render('amount') !!}</label>
                    @if(! empty($proposal['options']['amount']))
                        <select id="qe-amount" name="amount" class="form-select {{ $mark('amount') }} @error('amount') is-invalid @enderror" required>
                            <option value="">Escolha o valor…</option>
                            @foreach($proposal['options']['amount'] as $o)
                                <option value="{{ $o['id'] }}" @selected((string) old('amount', $initial('amount')) === (string) $o['id'])>{{ $o['label'] }}</option>
                            @endforeach
                        </select>
                    @else
                        <input id="qe-amount" type="text" inputmode="decimal" name="amount" value="{{ old('amount', $amountText($initial('amount'))) }}"
                               class="form-control {{ $mark('amount') }} @error('amount') is-invalid @enderror" placeholder="ex.: 922,50" required>
                    @endif
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold" for="qe-date">Data <span class="text-danger">*</span>{!! $render('expense_date') !!}</label>
                    <input id="qe-date" type="date" name="expense_date" value="{{ $current('expense_date') }}" class="form-control {{ $mark('expense_date') }} @error('expense_date') is-invalid @enderror" required>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold" for="qe-category">Categoria{!! $render('category') !!}</label>
                    <select id="qe-category" name="category" class="form-select {{ $mark('category') }} @error('category') is-invalid @enderror">
                        <option value="">{{ in_array('category', $unresolved, true) ? 'Escolha…' : '— Sem categoria —' }}</option>
                        @if(in_array('category', $unresolved, true))<option value="{{ $none }}" @selected(old('category') === $none)>Deixar em branco</option>@endif
                        @foreach(\App\Models\Expense::categories() as $v => $l)
                            <option value="{{ $v }}" @selected($current('category') === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold" for="qe-status">Estado <span class="text-danger">*</span>{!! $render('status') !!}</label>
                    <select id="qe-status" name="status" class="form-select {{ $mark('status') }} @error('status') is-invalid @enderror" required>
                        <option value="">Escolha…</option>
                        @foreach(\App\Models\Expense::statuses() as $v => $l)
                            <option value="{{ $v }}" @selected($current('status') === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold" for="qe-method">Método de pagamento{!! $render('payment_method') !!}</label>
                    <select id="qe-method" name="payment_method" class="form-select {{ $mark('payment_method') }} @error('payment_method') is-invalid @enderror">
                        <option value="">{{ in_array('payment_method', $unresolved, true) ? 'Escolha…' : '— Não indicado —' }}</option>
                        @if(in_array('payment_method', $unresolved, true))<option value="{{ $none }}" @selected(old('payment_method') === $none)>Deixar em branco</option>@endif
                        @foreach(\App\Models\Expense::paymentMethods() as $v => $l)
                            <option value="{{ $v }}" @selected($current('payment_method') === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold" for="qe-client">Cliente{!! $render('client_id') !!}</label>
                    @php $clientOptionIds = collect($proposal['options']['client_id'] ?? [])->pluck('id')->all(); @endphp
                    <select id="qe-client" name="client_id" class="form-select {{ $mark('client_id') }} @error('client_id') is-invalid @enderror">
                        <option value="">{{ in_array('client_id', $unresolved, true) ? 'Escolha…' : '— Sem cliente —' }}</option>
                        @if(in_array('client_id', $unresolved, true))<option value="{{ $none }}" @selected(old('client_id') === $none)>Deixar em branco</option>@endif
                        @if($clientOptionIds)
                            <optgroup label="Sugestões da frase">
                                @foreach($proposal['options']['client_id'] as $o)
                                    <option value="{{ $o['id'] }}" @selected((string) $current('client_id') === (string) $o['id'])>{{ $o['label'] }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                        <optgroup label="Todos os clientes">
                            @foreach($clients as $c)
                                @continue(in_array($c->id, $clientOptionIds))
                                <option value="{{ $c->id }}" @selected((string) $current('client_id') === (string) $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold" for="qe-legalization">Legalização{!! $render('legalization_id') !!}</label>
                    @php $legalOptionIds = collect($proposal['options']['legalization_id'] ?? [])->pluck('id')->all(); @endphp
                    <select id="qe-legalization" name="legalization_id" class="form-select {{ $mark('legalization_id') }} @error('legalization_id') is-invalid @enderror">
                        <option value="">{{ in_array('legalization_id', $unresolved, true) ? 'Escolha…' : '— Sem legalização —' }}</option>
                        @if(in_array('legalization_id', $unresolved, true))<option value="{{ $none }}" @selected(old('legalization_id') === $none)>Deixar em branco</option>@endif
                        @if($legalOptionIds)
                            <optgroup label="Sugestões da frase">
                                @foreach($proposal['options']['legalization_id'] as $o)
                                    <option value="{{ $o['id'] }}" @selected((string) $current('legalization_id') === (string) $o['id'])>{{ $o['label'] }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                        <optgroup label="Todas as legalizações">
                            @foreach($legalizations as $l)
                                @continue(in_array($l->id, $legalOptionIds))
                                <option value="{{ $l->id }}" @selected((string) $current('legalization_id') === (string) $l->id)>{{ $resolver->legalizationLabel($l) }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>

                <div class="col-12 col-md-8">
                    <label class="form-label fw-semibold" for="qe-title">Título <span class="text-danger">*</span>{!! $render('title') !!}</label>
                    <input id="qe-title" type="text" name="title" maxlength="255" value="{{ $current('title') }}" class="form-control @error('title') is-invalid @enderror" required>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold" for="qe-vat">IVA{!! $render('vat_rate') !!}</label>
                    <select id="qe-vat" name="vat_rate" class="form-select @error('vat_rate') is-invalid @enderror">
                        @foreach([0 => 'Sem IVA', 6 => '6%', 13 => '13%', 19 => '19%', 23 => '23%', 25 => '25%'] as $v => $l)
                            <option value="{{ $v }}" @selected((string) $current('vat_rate') === (string) $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold" for="qe-obs">Observações{!! $render('observations') !!}</label>
                    <textarea id="qe-obs" name="observations" rows="2" class="form-control @error('observations') is-invalid @enderror">{{ $current('observations') }}</textarea>
                </div>
            </div>

            @if($pending)
                <div class="d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-success" id="qe-submit"><i class="bi bi-check-lg me-1"></i> {{ $proposal['confirm_label'] }}</button>
                    <button type="submit" class="btn btn-outline-secondary" form="qe-cancel" formnovalidate>Cancelar</button>
                </div>
            @endif
        </form>
        @endif
        @if($pending)
            <form method="POST" action="{{ route($prefix . 'cancel', $interpretation->id) }}" id="qe-cancel">@csrf</form>
        @endif
    @endif
@endif

@push('scripts')
<script>
    document.querySelectorAll('.qe-example').forEach(function (b) {
        b.addEventListener('click', function () {
            var t = document.getElementById('qe-text');
            t.value = b.dataset.text; t.focus();
        });
    });
    // Duplo clique: o botão fica desativado depois do primeiro envio (o servidor também ignora repetições).
    var confirmForm = document.getElementById('qe-confirm');
    if (confirmForm) {
        confirmForm.addEventListener('submit', function (e) {
            if (e.submitter && e.submitter.getAttribute('form') === 'qe-cancel') { return; }
            if (confirmForm.dataset.sent) { e.preventDefault(); return; }
            confirmForm.dataset.sent = '1';
            var btn = document.getElementById('qe-submit');
            if (btn) { btn.disabled = true; btn.innerHTML = 'A criar…'; }
        });
    }
</script>
@endpush
