@extends($layout)

@section('title', 'Inserção rápida')

@if($mobile)
    @section('header_actions')
        <a href="{{ route('admin.mobile.dashboard') }}" class="order-first m-topbar-back" aria-label="Voltar"><i class="bi bi-chevron-left fs-4"></i></a>
    @endsection
@endif

@section('content')
    @unless($mobile)
        @include('components.admin.page-header', [
            'breadcrumbs' => [
                ['icon' => 'bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
                ['icon' => 'bi-lightning-charge', 'label' => 'Inserção rápida'],
            ],
            'title' => 'Inserção rápida',
            'subtitle' => 'Escreva ou dite uma frase, reveja a proposta e confirme. Nada é gravado antes da confirmação.',
        ])
    @endunless

    @include('admin.quick-entry._body')
@endsection
