@extends('frontend.partials.layout')

@section('title', 'Izzycar - Termos e Condições')

@section('content')
<section class="hero-section d-flex justify-content-center align-items-center" id="section_terms">
    <div class="container">
        @php
        $settings = \App\Models\Setting::all();
        @endphp
        <h1 class="text-accent">Termos e Condições</h1>
        <p>O website www.izzycar.pt é propriedade da Izzycar , NIF <span class="text-accent">{{$settings->where('label', 'vat_number')->first()->value}}</span>, com sede em <span class="text-accent">{{$settings->where('label', 'address')->first()->value}}</span>.
            A utilização deste website implica a aceitação dos presentes Termos e Condições.</p>

        <h6 class="text-accent">1. Âmbito</h6>
        <p>A Izzycar dedica-se a serviços de aconselhamento automóvel, importação e venda de veículos.
            Este website destina-se à divulgação de serviços, disponibilização de informações e receção de pedidos de contacto.</p>

        <h6 class="text-accent">2. Utilização do site</h6>
        <p>O utilizador compromete-se a fornecer informações verdadeiras e completas ao utilizar os formulários do site
            e a não utilizar este serviço para fins ilícitos.</p>

        <h6 class="text-accent">3. Informação e preços</h6>
        <p>A Izzycar procura manter toda a informação atualizada e correta.
            No entanto, os valores apresentados para veículos, taxas e serviços são meramente informativos e podem ser alterados sem aviso prévio
            devido a variações legais, fiscais ou alfandegárias.</p>

        <h6 class="text-accent">4. Propriedade intelectual</h6>
        <p>Todos os conteúdos do site (textos, imagens, logótipos e design) são propriedade da Izzycar
            e não podem ser copiados, reproduzidos ou utilizados sem autorização prévia.</p>

        <h6 class="text-accent">5. Responsabilidade</h6>
        <p>A Izzycar não se responsabiliza por interrupções no acesso ao site, erros técnicos, ou por conteúdos de links externos.</p>

        <h6 class="text-accent">6. Dados pessoais</h6>
        <p>O tratamento de dados pessoais rege-se pela nossa <a class="text-accent" href="{{route('frontend.privacy')}}">Política de Privacidade</a>.</p>

        <h6 class="text-accent">7. Lei aplicável</h6>
        <p>Estes Termos regem-se pela lei portuguesa. Em caso de litígio, será competente o foro legal aplicável em Portugal.</p>
    </div>
</section>
@endsection