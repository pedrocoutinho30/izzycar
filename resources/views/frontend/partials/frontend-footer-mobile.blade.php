<footer class="site-footer section-padding">
    <div class="container">
        @php
        $settings = \App\Models\Setting::all();
        @endphp
        <div class="row g-1 align-items-center text-center">

            <div class="col-12 col-lg-12">
                <a href="{{ route('vehicles.list') }}">
                    <h6 class="site-footer-title mb-0">Home</h6>
                </a>
            </div>

            <div class="col-12 col-lg-12">

                <a href="{{ route('frontend.import') }}">
                    <h6 class="site-footer-title mb-0">Importação</h6>
                </a>
            </div>
            <div class="col-12 col-lg-12">
                <a href="{{ route('frontend.legalization') }}">
                    <h6 class="site-footer-title mb-0">Legalização</h6>
                </a>
            </div>

            <div class="col-12 col-lg-12">
                <a href="{{ route('vehicles.list') }}">
                    <h6 class="site-footer-title mb-0">Usados</h6>
                </a>
            </div>
            <div class="col-12 col-lg-12">
                <div class="text-center mb-3">
                    <a href="{{ route('frontend.form-import') }}" class="btn btn-warning btn-lg mt-4">
                        Quero importar
                    </a>
                </div>
            </div>
            <div class="col-12 col-lg-12">
                <h6 class="site-footer-title mb-0">Contactos</h6>
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