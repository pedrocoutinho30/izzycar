{{-- Campos editáveis de um pedido de importação (criar manual / editar).
     $formProposal pode ser null; $useOld liga os valores antigos da sessão. --}}
@php
    $f = $formProposal ?? null;
    $useOld = $useOld ?? true;
    $val = fn ($field) => $useOld ? old($field, $f?->{$field}) : $f?->{$field};
    $err = fn ($field) => $useOld && $errors->has($field) ? 'is-invalid' : '';
@endphp

<div class="row g-3">
    <div class="col-12">
        <label class="form-label">Nome do pedido</label>
        <input type="text" name="title" class="form-control {{ $err('title') }}" value="{{ $val('title') }}" maxlength="120" placeholder="Ex.: Segundo carro, Carro para a filha…">
        <div class="form-text">Opcional — por omissão usa a marca e modelo pretendidos.</div>
        @if($useOld) @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
    </div>
    @include('admin.v2.partials._brand-model-select', [
        'brand' => $val('brand'),
        'model' => $val('model'),
        'required' => false,
        'colClass' => 'col-md-4',
        'idPrefix' => $f ? 'requestEdit' : 'requestNew',
        'brandError' => $err('brand'),
        'modelError' => $err('model'),
    ])
    <div class="col-md-4">
        <label class="form-label">Versão</label>
        <input type="text" name="version" class="form-control {{ $err('version') }}" value="{{ $val('version') }}" maxlength="255">
    </div>
    <div class="col-md-3">
        <label class="form-label">Combustível</label>
        <select name="fuel" class="form-select {{ $err('fuel') }}">
            <option value="">—</option>
            @foreach(\App\Enums\VehicleFuel::options() as $value => $label)
            <option value="{{ $value }}" @selected($val('fuel') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Ano mínimo</label>
        <input type="number" name="year_min" class="form-control {{ $err('year_min') }}" value="{{ $val('year_min') }}" min="1950" max="{{ now()->year + 1 }}">
        @if($useOld) @error('year_min')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
    </div>
    <div class="col-md-3">
        <label class="form-label">Km máximos</label>
        <input type="number" name="km_max" class="form-control {{ $err('km_max') }}" value="{{ $val('km_max') }}" min="0" step="1">
        @if($useOld) @error('km_max')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
    </div>
    <div class="col-md-3">
        <label class="form-label">Orçamento</label>
        <div class="input-group">
            <input type="number" name="budget" class="form-control {{ $err('budget') }}" value="{{ $val('budget') }}" min="0" step="1">
            <span class="input-group-text">€</span>
        </div>
        @if($useOld) @error('budget')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror @endif
    </div>
    <div class="col-12">
        <label class="form-label">Notas</label>
        <textarea name="message" rows="3" class="form-control {{ $err('message') }}" placeholder="Ex.: Ligou a pedir um SUV elétrico para a família, até 35 mil.">{{ $val('message') }}</textarea>
    </div>
</div>
