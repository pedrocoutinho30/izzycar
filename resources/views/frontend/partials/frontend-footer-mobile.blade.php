<footer class="footer-modern-mobile">
    <div class="container">
        @php
        $settings = \App\Models\Setting::all();
        @endphp
        
        <!-- Brand -->
        <div class="footer-mobile-brand">
            <h3 class="footer-mobile-logo">Izzycar</h3>
            <p class="footer-mobile-tagline">Importação de veículos com transparência</p>
        </div>

        <!-- Navigation -->
        <div class="footer-mobile-nav">
            @foreach($menus->where('parent_id', null) as $menu)
            @if($menu->title !== 'Notícias')
            <a href="{{ $menu->url }}" class="footer-mobile-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
                {{ $menu->title }}
            </a>
            @endif
            @endforeach
        </div>

        <!-- Contact -->
        <div class="footer-mobile-contact">
            <h5 class="footer-mobile-title">Contacto</h5>
            <a href="tel:{{$settings->where('label', 'phone')->first()->value}}" class="contact-mobile-item">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                </svg>
                {{$settings->where('label', 'phone')->first()->value}}
            </a>
            <a href="mailto:{{$settings->where('label', 'email')->first()->value}}" class="contact-mobile-item">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
                {{$settings->where('label', 'email')->first()->value}}
            </a>
        </div>

        <!-- Social -->
        <div class="footer-mobile-social">
            <a href="{{$settings->where('label', 'facebook')->first()->value}}" target="_blank" class="social-mobile-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
            </a>
            <a href="{{$settings->where('label', 'insta')->first()->value}}" target="_blank" class="social-mobile-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                </svg>
            </a>
        </div>

        <!-- CTA -->
        <div class="footer-mobile-cta">
            <a href="{{ route('frontend.form-import') }}" class="btn-mobile-cta">
                Quero importar
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        </div>

        <!-- Bottom -->
        <div class="footer-mobile-bottom">
            <div class="footer-mobile-legal">
                <a href="{{ route('frontend.privacy') }}" target="_blank" class="legal-mobile-link">Privacidade</a>
                <span>•</span>
                <a href="{{ route('frontend.terms') }}" target="_blank" class="legal-mobile-link">Termos</a>
            </div>
            <p class="footer-mobile-copy">© 2025 Izzycar</p>
        </div>
    </div>
</footer>

<style>
    .footer-modern-mobile {
        background: linear-gradient(135deg, #111111 0%, #1a1a1a 100%);
        color: white;
        padding: 2.5rem 0 1.5rem;
    }

    .footer-mobile-brand {
        text-align: center;
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .footer-mobile-logo {
        font-size: 1.75rem;
        font-weight: 800;
        color: white;
        margin-bottom: 0.5rem;
        background: linear-gradient(135deg, white 0%, var(--yellow-color) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .footer-mobile-tagline {
        font-size: 0.9rem;
        color: #b8b8b8;
        margin: 0;
    }

    .footer-mobile-nav {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-bottom: 2rem;
    }

    .footer-mobile-link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0.875rem 1rem;
        background: rgba(255,255,255,0.03);
        border-radius: 12px;
        color: #b8b8b8;
        text-decoration: none;
        font-size: 0.95rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .footer-mobile-link:active {
        background: rgba(255,255,255,0.08);
        color: white;
    }

    .footer-mobile-link svg {
        color: var(--accent-color);
    }

    .footer-mobile-contact {
        margin-bottom: 2rem;
    }

    .footer-mobile-title {
        font-size: 1rem;
        font-weight: 700;
        color: white;
        margin-bottom: 1rem;
        text-align: center;
    }

    .contact-mobile-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 0.875rem 1rem;
        background: rgba(255,255,255,0.03);
        border-radius: 12px;
        color: #b8b8b8;
        text-decoration: none;
        font-size: 0.9rem;
        margin-bottom: 0.75rem;
        transition: all 0.3s ease;
    }

    .contact-mobile-item:active {
        background: rgba(255,255,255,0.08);
        color: white;
    }

    .contact-mobile-item svg {
        color: var(--accent-color);
        flex-shrink: 0;
    }

    .footer-mobile-social {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .social-mobile-link {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 50%;
        color: white;
        transition: all 0.3s ease;
    }

    .social-mobile-link:active {
        background: var(--accent-color);
        border-color: var(--accent-color);
        transform: scale(0.95);
    }

    .footer-mobile-cta {
        margin-bottom: 2rem;
    }

    .btn-mobile-cta {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, var(--accent-color) 0%, #990000 100%);
        color: white;
        font-weight: 700;
        font-size: 1rem;
        border-radius: 50px;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(110, 7, 7, 0.3);
    }

    .btn-mobile-cta:active {
        transform: scale(0.98);
        box-shadow: 0 2px 10px rgba(110, 7, 7, 0.4);
    }

    .footer-mobile-bottom {
        text-align: center;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(255,255,255,0.1);
    }

    .footer-mobile-legal {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .legal-mobile-link {
        color: #6c757d;
        text-decoration: none;
        font-size: 0.85rem;
        transition: color 0.3s ease;
    }

    .legal-mobile-link:active {
        color: var(--accent-color);
    }

    .footer-mobile-legal span {
        color: #6c757d;
    }

    .footer-mobile-copy {
        margin: 0;
        color: #6c757d;
        font-size: 0.85rem;
    }
</style>