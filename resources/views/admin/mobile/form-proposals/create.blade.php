@extends('layouts.admin-mobile')

@section('title', 'Novo pedido')

@php
    $voltar = match (true) {
        (bool) $client->is_lead => route('admin.mobile.leads.show', $client->id),
        \Illuminate\Support\Facades\Route::has('admin.mobile.clients.show') => route('admin.mobile.clients.show', $client->id),
        default => route('admin.mobile.form-proposals.index'),
    };
@endphp

@section('header_actions')
    <a href="{{ $voltar }}" class="order-first m-topbar-back" aria-label="Voltar"><i class="bi bi-chevron-left fs-4"></i></a>
@endsection

@section('content')
    <form action="{{ route('admin.mobile.form-proposals.store') }}" method="POST" class="m-card d-grid gap-3">
        @csrf
        <input type="hidden" name="client_id" value="{{ $client->id }}">

        <div>
            <div class="fw-semibold">{{ $client->name }}</div>
            <div class="small text-muted">
                Use quando o cliente pediu diretamente (sem o formulário do site) ou quer um novo carro — as oportunidades ficam agrupadas neste pedido. Todos os campos são opcionais.
            </div>
        </div>

        <div>
            <label for="fp-title" class="form-label fw-semibold">Nome do pedido</label>
            <input type="text" id="fp-title" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror"
                   maxlength="120" placeholder="Ex.: Segundo carro, Carro para a filha…" enterkeyhint="next">
            <div class="form-text">Opcional — por omissão usa a marca e modelo pretendidos.</div>
        </div>

        @include('admin.mobile.form-proposals._brand-model', [
            'brand' => old('brand'),
            'model' => old('model'),
            'required' => false,
            'idPrefix' => 'fp',
        ])

        <div>
            <label for="fp-version" class="form-label fw-semibold">Versão</label>
            <input type="text" id="fp-version" name="version" value="{{ old('version') }}" class="form-control" maxlength="255">
        </div>

        <div>
            <label for="fp-fuel" class="form-label fw-semibold">Combustível</label>
            <select id="fp-fuel" name="fuel" class="form-select">
                <option value="">—</option>
                @foreach(\App\Enums\VehicleFuel::options() as $valor => $label)
                    <option value="{{ $valor }}" @selected(old('fuel') === $valor)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="fp-year" class="form-label fw-semibold">Ano mínimo</label>
            <input type="number" id="fp-year" name="year_min" value="{{ old('year_min') }}" class="form-control @error('year_min') is-invalid @enderror"
                   min="1950" max="{{ now()->year + 1 }}" inputmode="numeric">
        </div>

        <div>
            <label for="fp-km" class="form-label fw-semibold">Km máximos</label>
            <input type="number" id="fp-km" name="km_max" value="{{ old('km_max') }}" class="form-control @error('km_max') is-invalid @enderror"
                   min="0" step="1" inputmode="numeric">
        </div>

        <div>
            <label for="fp-budget" class="form-label fw-semibold">Orçamento (€)</label>
            <input type="number" id="fp-budget" name="budget" value="{{ old('budget') }}" class="form-control @error('budget') is-invalid @enderror"
                   min="0" step="1" inputmode="numeric">
        </div>

        <div>
            <label for="fp-message" class="form-label fw-semibold">Notas</label>
            <textarea id="fp-message" name="message" rows="3" maxlength="5000" class="form-control"
                      placeholder="Ex.: Ligou a pedir um SUV elétrico para a família, até 35 mil.">{{ old('message') }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-lg fw-semibold">Criar pedido</button>
    </form>
@endsection
