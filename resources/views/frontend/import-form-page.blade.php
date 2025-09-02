@extends('frontend.partials.layout')

@section('title', 'Izzycar - Formulário de Importação')

@section('content')

<section class="explore-section section-padding" class="bg-dark rounded shadow-sm">
    <div class="container">
        <h4 class=" mb-3 mt-3 text-accent" id="formPropostaLabel">Peça já a sua proposta</h4>
        @include('frontend.forms.proposal', ['brands' => $brands])
    </div>
</section>
@endsection