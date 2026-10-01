{{-- Aviso de contacto já existente (ClientMatcher::duplicateWarning): mostra
     quem é, com ligação à ficha, e deixa criar mesmo assim (ex. duas
     pessoas que partilham o telefone). --}}
@if($errors->has('duplicate'))
<div class="alert alert-warning">
    <div class="mb-2"><i class="bi bi-exclamation-triangle-fill me-1"></i>
        {{ $errors->first('duplicate') }}
        @if(session('duplicate_url'))
            <a href="{{ session('duplicate_url') }}" target="_blank" rel="noopener">Abrir ficha</a>
        @endif
    </div>
    <div class="form-check mb-0">
        <input class="form-check-input" type="checkbox" name="confirm_duplicate" value="1" id="confirmDuplicate">
        <label class="form-check-label" for="confirmDuplicate">É outra pessoa — criar mesmo assim</label>
    </div>
</div>
@endif
