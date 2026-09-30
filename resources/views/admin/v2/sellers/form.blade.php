@extends('layouts.admin-v2')

@section('title', $seller ? 'Editar Vendedor' : 'Novo Vendedor')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => array_values(array_filter([
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-shop', 'label' => 'Vendedores', 'href' => route('admin.v2.sellers.index')],
        $seller ? ['icon' => '', 'label' => $seller->name, 'href' => route('admin.v2.sellers.show', $seller->id)] : null,
        ['icon' => '', 'label' => $seller ? 'Editar' : 'Novo'],
    ])),
    'title' => $seller ? 'Editar vendedor' : 'Novo vendedor',
    'subtitle' => $seller ? $seller->name : 'Empresa ou stand — as pessoas ficam nos contactos',
])

@if($errors->any())
<div class="alert alert-danger mb-3">
    <i class="bi bi-exclamation-triangle me-2"></i>Verifique os campos assinalados.
    <ul class="mb-0 mt-1 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<form action="{{ $seller ? route('admin.v2.sellers.update', $seller->id) : route('admin.v2.sellers.store') }}" method="POST"
      data-seller-form="{{ $seller ? 'seller-edit' : 'seller' }}"
      @if($seller) data-seller-id="{{ $seller->id }}" @endif
      @if($errors->has('matches')) data-recheck @endif>
    @csrf
    @if($seller)
        @method('PUT')
    @endif
    <input type="hidden" name="confirm_matches" value="0">
    @unless($seller)
        <input type="hidden" name="existing_seller_id" value="">
    @endunless

    <div class="row g-4">
        <div class="col-lg-8">
            <div data-dup-alert></div>

            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-shop"></i>
                        Dados do vendedor
                    </h5>
                </div>
                @include('admin.v2.sellers._seller-fields', ['seller' => $seller])
            </div>

            @unless($seller)
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-person"></i>
                        Primeiro contacto
                    </h5>
                    <span class="small text-muted">Opcional — pode adicionar mais na ficha do vendedor</span>
                </div>
                @include('admin.v2.sellers._contact-fields', ['contact' => null, 'required' => false, 'showPrimary' => false])
            </div>
            @endunless
        </div>

        <div class="col-lg-4">
            @include('components.admin.action-card', [
                'cancelButtonHref' => $seller ? route('admin.v2.sellers.show', $seller->id) : route('admin.v2.sellers.index'),
                'submitButtonLabel' => $seller ? 'Guardar alterações' : 'Criar vendedor',
                'timestamps' => $seller ? ['created_at' => $seller->created_at, 'updated_at' => $seller->updated_at] : null,
            ])
        </div>
    </div>
</form>

@include('admin.v2.sellers._scripts')
@endsection
