<footer class="footer-modern">
    <div class="footer-main">
        <div class="container">
            @php
            $settings = \App\Models\Setting::all();
            @endphp
            
            <div class="row g-4">
                <!-- Brand Section -->
                <div class="col-lg-4">
                    <div class="footer-brand">
                        <h3 class="footer-logo">Izzycar</h3>
                        <p class="footer-tagline">Importação de veículos de qualidade com transparência e confiança</p>
                        <div class="footer-social">
                            <a href="{{$settings->where('label', 'facebook')->first()->value}}" target="_blank" class="social-link">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </a>
                            <a href="{{$settings->where('label', 'insta')->first()->value}}" target="_blank" class="social-link">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="col-lg-4">
                    <div class="footer-section">
                        <h4 class="footer-section-title">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="3" y1="12" x2="21" y2="12"></line>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <line x1="3" y1="18" x2="21" y2="18"></line>
                            </svg>
                            Navegação
                        </h4>
                        <ul class="footer-links">
                            @foreach($menus->where('parent_id', null) as $menu)
                            @if($menu->title !== 'Notícias')
                            <li>
                                <a href="{{ $menu->url }}" class="footer-link">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="9 18 15 12 9 6"></polyline>
                                    </svg>
                                    {{ $menu->title }}
                                </a>
                            </li>
                            @endif
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Contact Section -->
                <div class="col-lg-4">
                    <div class="footer-section">
                        <h4 class="footer-section-title">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            Contacto
                        </h4>
                        <ul class="footer-contact">
                            <li class="contact-item">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                                <a href="tel:{{$settings->where('label', 'phone')->first()->value}}" class="contact-link">
                                    {{$settings->where('label', 'phone')->first()->value}}
                                </a>
                            </li>
                            <li class="contact-item">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                                <a href="mailto:{{$settings->where('label', 'email')->first()->value}}" class="contact-link">
                                    {{$settings->where('label', 'email')->first()->value}}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="footer-cta">
                <div class="cta-card">
                    <div class="cta-content">
                        <h3 class="cta-title">Pronto para importar o seu veículo?</h3>
                        <p class="cta-subtitle">Comece agora e receba uma proposta personalizada</p>
                    </div>
                    <a href="{{ route('frontend.form-import') }}" class="btn-cta-footer">
                        Quero importar
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <div class="container">
            <div class="footer-bottom-content">
                <p class="footer-copyright">© 2025 Izzycar. Todos os direitos reservados.</p>
                <div class="footer-legal">
                    <a href="{{ route('frontend.privacy') }}" target="_blank" class="legal-link">Política de Privacidade</a>
                    <span class="separator">•</span>
                    <a href="{{ route('frontend.terms') }}" target="_blank" class="legal-link">Termos e Condições</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
    .footer-modern {
        background: linear-gradient(135deg, #111111 0%, #1a1a1a 100%);
        color: white;
    }

    .footer-main {
        padding: 4rem 0 2rem;
    }

    .footer-brand {
        padding-right: 2rem;
    }

    .footer-logo {
        font-size: 2rem;
        font-weight: 800;
        color: white;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, white 0%, var(--yellow-color) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .footer-tagline {
        font-size: 0.95rem;
        color: #b8b8b8;
        line-height: 1.6;
        margin-bottom: 1.5rem;
    }

    .footer-social {
        display: flex;
        gap: 1rem;
    }

    .social-link {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 50%;
        color: white;
        transition: all 0.3s ease;
    }

    .social-link:hover {
        background: var(--accent-color);
        border-color: var(--accent-color);
        transform: translateY(-3px);
    }

    .footer-section {
        height: 100%;
    }

    .footer-section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: white;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .footer-section-title svg {
        color: var(--accent-color);
    }

    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-links li {
        margin-bottom: 0.75rem;
    }

    .footer-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #b8b8b8;
        text-decoration: none;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .footer-link svg {
        opacity: 0;
        transform: translateX(-5px);
        transition: all 0.3s ease;
    }

    .footer-link:hover {
        color: var(--accent-color);
        padding-left: 5px;
    }

    .footer-link:hover svg {
        opacity: 1;
        transform: translateX(0);
    }

    .footer-contact {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .contact-item {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 1rem;
        padding: 0.75rem;
        background: rgba(255,255,255,0.03);
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .contact-item:hover {
        background: rgba(255,255,255,0.05);
    }

    .contact-item svg {
        color: var(--accent-color);
        flex-shrink: 0;
    }

    .contact-link {
        color: #b8b8b8;
        text-decoration: none;
        font-size: 0.95rem;
        transition: color 0.3s ease;
    }

    .contact-link:hover {
        color: white;
    }

    .footer-cta {
        margin-top: 3rem;
        padding-top: 3rem;
        border-top: 1px solid rgba(255,255,255,0.1);
    }

    .cta-card {
        background: linear-gradient(135deg, var(--accent-color) 0%, #990000 100%);
        border-radius: 20px;
        padding: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 2rem;
    }

    .cta-content {
        flex: 1;
    }

    .cta-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: white;
        margin-bottom: 0.5rem;
    }

    .cta-subtitle {
        font-size: 1rem;
        color: rgba(255,255,255,0.9);
        margin: 0;
    }

    .btn-cta-footer {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 1rem 2rem;
        background: white;
        color: #111;
        font-weight: 700;
        font-size: 1.05rem;
        border-radius: 50px;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }

    .btn-cta-footer:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        color: #111;
    }

    .btn-cta-footer svg {
        transition: transform 0.3s ease;
    }

    .btn-cta-footer:hover svg {
        transform: translateX(5px);
    }

    .footer-bottom {
        background: #0a0a0a;
        padding: 1.5rem 0;
        border-top: 1px solid rgba(255,255,255,0.05);
    }

    .footer-bottom-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .footer-copyright {
        margin: 0;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .footer-legal {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .legal-link {
        color: #6c757d;
        text-decoration: none;
        font-size: 0.9rem;
        transition: color 0.3s ease;
    }

    .legal-link:hover {
        color: var(--accent-color);
    }

    .separator {
        color: #6c757d;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .footer-main {
            padding: 3rem 0 1.5rem;
        }

        .footer-brand {
            padding-right: 0;
            margin-bottom: 2rem;
        }

        .cta-card {
            flex-direction: column;
            text-align: center;
            padding: 2rem;
        }

        .cta-title {
            font-size: 1.5rem;
        }

        .footer-bottom-content {
            flex-direction: column;
            text-align: center;
        }
    }

    @media (max-width: 768px) {
        .footer-cta {
            margin-top: 2rem;
            padding-top: 2rem;
        }

        .cta-title {
            font-size: 1.25rem;
        }

        .cta-subtitle {
            font-size: 0.9rem;
        }
    }
</style>