@extends('frontend.partials.layout')

@section('title', 'Izzycar - Politicas de Privacidade')

@section('content')
<section class="hero-section d-flex justify-content-center align-items-center" id="section_privacy">
    <div class="container">
@php 
$settings = \App\Models\Setting::all();
@endphp
        <h1 class="text-accent">Política de Privacidade</h1>
        <p>Na Izzycar respeitamos a sua privacidade e estamos empenhados em proteger os seus dados pessoais.
            Esta Política explica como recolhemos, utilizamos e salvaguardamos a informação que nos fornece.</p>

        <h6 class="text-accent">1. Responsável pelo tratamento</h6>
        <p>A Izzycar, NIF <span class="text-accent">{{$settings->where('label', 'vat_number')->first()->value}}</span>, com sede em <span class="text-accent">{{$settings->where('label', 'address')->first()->value}}</span>, é responsável pelo tratamento dos seus dados pessoais.
            Pode contactar-nos através do email <a class ="text-accent" href="mailto:{{$settings->where('label', 'email')->first()->value}}">{{$settings->where('label', 'email')->first()->value}}</a> ou do telefone <span class="text-accent">{{$settings->where('label', 'phone')->first()->value}}</span>.</p>

        <h6 class="text-accent">2. Dados recolhidos</h6>
        <p>Podemos recolher os seguintes dados:</p>
        <ul>
            <li>Nome, email e telefone (quando nos contacta ou pede uma proposta);</li>
            <li>Dados de navegação (endereço IP, cookies, estatísticas de utilização do site);</li>
            <li>Informações adicionais fornecidas pelo utilizador nos formulários.</li>
        </ul>

        <h6 class="text-accent">3. Finalidade do tratamento</h6>
        <p>Os dados recolhidos destinam-se a:</p>
        <ul>
            <li>Responder a pedidos de contacto e enviar propostas;</li>
            <li>Gerir pedidos de importação e serviços contratados;</li>
            <li>Enviar comunicações informativas ou comerciais (quando autorizado);</li>
            <li>Melhorar a experiência de utilização do website.</li>
        </ul>

        <h6 class="text-accent">4. Partilha de dados</h6>
        <p>Podemos partilhar dados com prestadores de serviços (ex.: alojamento web, ferramentas de marketing, Google Analytics, redes sociais)
            apenas na medida necessária à prestação dos serviços. Não vendemos dados pessoais a terceiros.</p>

        <h6 class="text-accent">5. Conservação dos dados</h6>
        <p>Os dados pessoais são conservados pelo período necessário à finalidade a que se destinam,
            ou até que o utilizador exerça o direito de eliminação.</p>

        <h6 class="text-accent">6. Direitos do titular</h6>
        <p>O utilizador tem direito de acesso, retificação, eliminação, portabilidade e oposição ao tratamento dos seus dados pessoais.
            Para exercer estes direitos, contacte-nos através de <a class ="text-accent" href="mailto:{{$settings->where('label', 'email')->first()->value}}">{{$settings->where('label', 'email')->first()->value}}</a>.</p>

        <h6 class="text-accent">7. Cookies</h6>
        <p>Utilizamos cookies para melhorar a navegação e analisar estatísticas de utilização.
            O utilizador pode gerir as preferências de cookies no seu navegador.</p>

        <h6 class="text-accent">8. Alterações</h6>
        <p>Esta Política de Privacidade pode ser atualizada. Última atualização: 2025.</p>

    </div>
</section>
@endsection