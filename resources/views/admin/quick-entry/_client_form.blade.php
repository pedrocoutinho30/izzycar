@php
    $sourceBadge = ['extracted' => 'bg-success', 'inferred' => 'bg-info text-dark', 'default' => 'bg-secondary', 'relation' => 'bg-primary'];
    $badge = function (string $f) use ($fields, $sourceLabels, $sourceBadge) {
        $s = $fields[$f]['source'] ?? null;
        return $s ? '<span class="badge ' . $sourceBadge[$s] . ' ms-1">' . e($sourceLabels[$s]) . '</span>' : '';
    };
    $needsDuplicateConfirm = collect($proposal['warnings'] ?? [])->contains('field', 'confirm_duplicate') || $errors->has('confirm_duplicate');
@endphp

<form method="POST" action="{{ route($prefix . 'confirm', $interpretation->id) }}" id="qe-confirm" class="{{ $card }} d-grid gap-3" @if(! $pending) inert @endif>
    @csrf
    <div class="fw-semibold"><i class="bi bi-person-plus me-1"></i> Novo cliente (editável)</div>

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <label class="form-label fw-semibold" for="qe-name">Nome <span class="text-danger">*</span>{!! $badge('name') !!}</label>
            <input id="qe-name" type="text" name="name" maxlength="255" value="{{ $current('name') }}" class="form-control @error('name') is-invalid @enderror" required>
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label fw-semibold" for="qe-phone">Telefone{!! $badge('phone') !!}</label>
            <input id="qe-phone" type="text" inputmode="tel" name="phone" value="{{ $current('phone') }}" class="form-control @error('phone') is-invalid @enderror">
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label fw-semibold" for="qe-email">Email{!! $badge('email') !!}</label>
            <input id="qe-email" type="email" name="email" maxlength="255" value="{{ $current('email') }}" class="form-control @error('email') is-invalid @enderror">
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label fw-semibold" for="qe-nif">NIF{!! $badge('vat_number') !!}</label>
            <input id="qe-nif" type="text" inputmode="numeric" name="vat_number" maxlength="9" value="{{ $current('vat_number') }}" class="form-control @error('vat_number') is-invalid @enderror">
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label fw-semibold" for="qe-lang">Idioma{!! $badge('language') !!}</label>
            <select id="qe-lang" name="language" class="form-select">
                @foreach(['pt' => 'Português', 'en' => 'Inglês'] as $v => $l)
                    <option value="{{ $v }}" @selected($current('language') === $v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold" for="qe-observation">Observações{!! $badge('observation') !!}</label>
            <textarea id="qe-observation" name="observation" rows="2" class="form-control">{{ $current('observation') }}</textarea>
        </div>
    </div>

    @if($needsDuplicateConfirm)
        <div class="form-check">
            <input class="form-check-input @error('confirm_duplicate') is-invalid @enderror" type="checkbox" name="confirm_duplicate" value="1" id="qe-dup" @checked(old('confirm_duplicate'))>
            <label class="form-check-label" for="qe-dup">Já existe um registo com este telefone: criar mesmo assim.</label>
        </div>
    @endif

    @if($pending)
        <div class="d-flex gap-2 flex-wrap">
            <button type="submit" class="btn btn-success" id="qe-submit"><i class="bi bi-check-lg me-1"></i> {{ $proposal['confirm_label'] }}</button>
            <button type="submit" class="btn btn-outline-secondary" form="qe-cancel" formnovalidate>Cancelar</button>
        </div>
    @endif
</form>
