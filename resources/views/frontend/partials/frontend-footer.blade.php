<footer class="site-footer section-padding">
    <div class="container">
        @php
        $settings = \App\Models\Setting::all();
        @endphp
        <div class="row g-3 align-items-start">

            <div class="col-6 col-lg-3">
                <a href="{{ route('vehicles.list') }}">
                    <h6 class="site-footer-title mb-3">Home</h6>
                </a>
            </div>

            <div class="col-6 col-lg-3">
                <h6 class="site-footer-title mb-3">Serviços</h6>
                <ul class="site-footer-links">
                    <li class="site-footer-link-item">
                        <a href="{{ route('frontend.import') }}" class="site-footer-link">Importação</a>
                    </li>
                    <li class="site-footer-link-item">
                        <a href="{{ route('frontend.legalization') }}" class="site-footer-link">Legalização</a>
                    </li>
                </ul>
            </div>

            <div class="col-6 col-lg-3">
                <a href="{{ route('vehicles.list') }}">
                    <h6 class="site-footer-title mb-3">Usados</h6>
                </a>
            </div>

            <div class="col-6 col-lg-3">
                <h6 class="site-footer-title mb-3">Contactos</h6>
                <p class="mb-1">
                    <a href="tel:{{$settings->where('label', 'phone')->first()->value}}" class="site-footer-link">
                        {{$settings->where('label', 'phone')->first()->value}}
                    </a>
                </p>
                <p>
                    <a href="mailto:{{$settings->where('label', 'email')->first()->value}}" class="site-footer-link">
                        {{$settings->where('label', 'email')->first()->value}}
                    </a>
                </p>
                <div class="social-icons mt-3">
                    <a href="{{$settings->where('label', 'facebook')->first()->value}}" target="_blank" class="me-3 text-accent">
                        <i class="bi bi-facebook fs-4"></i>
                    </a>
                    <a href="{{$settings->where('label', 'insta')->first()->value}}" target="_blank" class="text-accent">
                        <i class="bi bi-instagram fs-4"></i>
                    </a>
                </div>

            </div>
        </div>
        <div class="text-center">
            <a href="{{ route('frontend.form-import') }}" class="btn btn-warning btn-lg mt-4">
                Quero importar
            </a>
        </div>
    </div>
    <div class="footer-bottom text-center mt-3">
        <div class="container">
            <small class="text-muted">
                <a target="_blank" href="{{ route('frontend.privacy') }}" class="footer-link text-white">Política de Privacidade</a> |
                <a target="_blank" href="{{ route('frontend.terms') }}" class="footer-link text-white">Termos e Condições</a>
                <br>
                © 2025 Izzycar
            </small>
        </div>
    </div>
</footer>

<style>
    .site-footer {
        padding: 20px 0;
        /* menos espaço em cima/baixo */
    }

    .site-footer .col-lg-3 {
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        /* ou center se quiseres centralizar o conteúdo */
        min-height: 120px;
        /* força todas a terem altura mínima igual */
    }
</style>