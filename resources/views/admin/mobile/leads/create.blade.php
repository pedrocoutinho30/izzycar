@extends('layouts.admin-mobile')

@section('title', 'Nova lead')

@section('header_actions')
    <a href="{{ route('admin.mobile.leads.index') }}" class="order-first m-topbar-back" aria-label="Voltar às leads"><i class="bi bi-chevron-left fs-4"></i></a>
@endsection

@section('content')
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $erro)<div>{{ $erro }}</div>@endforeach
        </div>
    @endif

    <form action="{{ route('admin.mobile.leads.store') }}" method="POST" class="m-card d-grid gap-3" style="font-size:16px">
        @csrf

        <div>
            <label for="lead-name" class="form-label fw-semibold">Nome <span class="text-danger">*</span></label>
            <input type="text" id="lead-name" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror"
                   required maxlength="255" autocomplete="name" autocapitalize="words" enterkeyhint="next">
        </div>

        <div>
            <label for="lead-phone" class="form-label fw-semibold">Telefone</label>
            <input type="tel" id="lead-phone" name="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror"
                   maxlength="50" inputmode="tel" autocomplete="tel" enterkeyhint="next">
        </div>

        <div>
            <label for="lead-email" class="form-label fw-semibold">Email</label>
            <input type="email" id="lead-email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror"
                   maxlength="255" inputmode="email" autocomplete="email" autocapitalize="none" enterkeyhint="next">
        </div>

        <div>
            <label for="lead-origin" class="form-label fw-semibold">Como chegou</label>
            <select id="lead-origin" name="origin" class="form-select">
                @foreach(['Manual BO' => 'Manual (BO)', 'Email' => 'Email recebido', 'Telefone' => 'Telefone / WhatsApp', 'Referência' => 'Referência', 'Instagram' => 'Instagram', 'Facebook' => 'Facebook', 'Outro' => 'Outro'] as $valor => $label)
                    <option value="{{ $valor }}" @selected(old('origin', 'Manual BO') === $valor)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="lead-status" class="form-label fw-semibold">Estado inicial</label>
            <select id="lead-status" name="lead_status" class="form-select">
                @foreach(['nova' => 'Nova', 'em_contacto' => 'Em contacto', 'fria' => 'Fria'] as $valor => $label)
                    <option value="{{ $valor }}" @selected(old('lead_status', 'nova') === $valor)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        @if($owners->isNotEmpty())
            <div>
                <label for="lead-owner" class="form-label fw-semibold">Angariador</label>
                <select id="lead-owner" name="owner_id" class="form-select">
                    <option value="">— Sem angariador —</option>
                    @foreach($owners as $dono)
                        <option value="{{ $dono->id }}" @selected(old('owner_id') == $dono->id)>{{ trim($dono->name . ' ' . $dono->last_name) }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div>
            <label for="lead-observation" class="form-label fw-semibold">Notas / contexto</label>
            <textarea id="lead-observation" name="observation" rows="3" maxlength="2000" class="form-control">{{ old('observation') }}</textarea>
        </div>

        @if($errors->has('duplicate'))
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="confirm_duplicate" value="1" id="confirm-duplicate">
                <label class="form-check-label" for="confirm-duplicate">Criar mesmo assim</label>
            </div>
        @endif

        <button type="submit" class="btn btn-primary btn-lg fw-semibold">Criar lead</button>
    </form>
@endsection
