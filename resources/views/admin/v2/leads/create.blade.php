@extends('layouts.admin-v2')

@section('title', 'Nova Lead')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => '', 'label' => 'Leads', 'href' => route('admin.v2.leads.index')],
        ['icon' => '', 'label' => 'Nova Lead']
    ],
    'title' => 'Nova Lead',
    'subtitle' => 'Cria uma lead manualmente a partir de um contacto recebido',
    'actionHref' => '',
    'actionLabel' => ''
])

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-person-plus"></i>
                    Dados da Lead
                </h5>
            </div>
            <div class="modern-card-body">

                @if($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('admin.v2.leads.store') }}" method="POST">
                    @csrf

                    {{-- Nome --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nome <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="Nome completo" autofocus>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Email + Telefone --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="email@exemplo.com">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Telefone</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone') }}" placeholder="+351 9XX XXX XXX">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Origem + Estado --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Como chegou</label>
                            <select name="origin" class="form-select @error('origin') is-invalid @enderror">
                                <option value="Manual BO" {{ old('origin', 'Manual BO') == 'Manual BO' ? 'selected' : '' }}>Manual (BO)</option>
                                <option value="Email" {{ old('origin') == 'Email' ? 'selected' : '' }}>Email recebido</option>
                                <option value="Telefone" {{ old('origin') == 'Telefone' ? 'selected' : '' }}>Telefone / WhatsApp</option>
                                <option value="Referência" {{ old('origin') == 'Referência' ? 'selected' : '' }}>Referência</option>
                                <option value="Instagram" {{ old('origin') == 'Instagram' ? 'selected' : '' }}>Instagram</option>
                                <option value="Facebook" {{ old('origin') == 'Facebook' ? 'selected' : '' }}>Facebook</option>
                                <option value="Outro" {{ old('origin') == 'Outro' ? 'selected' : '' }}>Outro</option>
                            </select>
                            @error('origin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Estado inicial</label>
                            <select name="lead_status" class="form-select @error('lead_status') is-invalid @enderror">
                                <option value="nova" {{ old('lead_status', 'nova') == 'nova' ? 'selected' : '' }}>Nova</option>
                                <option value="em_contacto" {{ old('lead_status') == 'em_contacto' ? 'selected' : '' }}>Em Contacto</option>
                                <option value="fria" {{ old('lead_status') == 'fria' ? 'selected' : '' }}>Fria</option>
                            </select>
                            @error('lead_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Notas --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Notas / Contexto</label>
                        <textarea name="observation" class="form-control @error('observation') is-invalid @enderror"
                                  rows="4" placeholder="Ex: Recebeu email a perguntar sobre importação de BMW Série 3, 2022, gasolina. Quer cotação rápida.">{{ old('observation') }}</textarea>
                        @error('observation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary-modern">
                            <i class="bi bi-person-plus me-1"></i>
                            Criar Lead
                        </button>
                        <a href="{{ route('admin.v2.leads.index') }}" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

@endsection
