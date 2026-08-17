<!-- Aviso de Cookies (discreto — barra fina no rodapé, não bloqueia a navegação) -->
<div id="cookieModal" class="cookie-banner">
    <div class="cookie-content shadow-lg">
        <p class="text-dark">
            Este site usa cookies para melhorar a sua experiência e analisar o tráfego.
            Ao continuar a navegar, ou ao clicar em "Aceitar", concorda com a sua utilização.
        </p>

        <div class="cookie-buttons">
            <button id="rejectAll">Rejeitar</button>
            <button id="acceptAll">Aceitar</button>
        </div>
    </div>
</div>

<style>
    /* Barra fina, fixa no rodapé — não cobre a página nem impede o scroll/cliques no resto do site */
    .cookie-banner {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 9999;
        pointer-events: none;
    }

    /* Sobe acima da barra de navegação fixa do mobile/tablet — e de qualquer
       CTA fixo extra que a própria página declare via --page-bottom-extra
       (ex.: o CTA fixo da página de detalhe da viatura). */
    @media (max-width: 991.98px) {
        .cookie-banner { bottom: calc(64px + env(safe-area-inset-bottom) + var(--page-bottom-extra, 0px)); }
    }

    .cookie-content {
        pointer-events: auto;
        background: #fff;
        padding: 12px 24px;
        box-shadow: 0 -2px 14px rgba(0, 0, 0, 0.15);
        border-top: 1px solid rgba(0, 0, 0, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 24px;
        flex-wrap: wrap;
        max-width: 1100px;
        margin: 0 auto;
    }

    .cookie-content p {
        margin: 0;
        font-size: 13px;
        line-height: 1.5;
        flex: 1 1 420px;
    }

    .cookie-buttons {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }

    .cookie-buttons button {
        padding: 6px 16px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 13px;
        white-space: nowrap;
    }

    #acceptAll {
        background: #6e0707;
        color: white;
    }

    #rejectAll {
        background: transparent;
        color: #555;
        border: 1px solid #ccc !important;
    }

    @media (max-width: 576px) {
        .cookie-content {
            justify-content: flex-start;
            padding: 12px 16px;
        }
    }
</style>

<script>
    const cookieModal = document.getElementById("cookieModal");
    const acceptAllBtn = document.getElementById("acceptAll");
    const rejectAllBtn = document.getElementById("rejectAll");
    loadAnalytics();
    // Verifica se já aceitou ou rejeitou cookies
    if (localStorage.getItem("cookies_choice")) {
        cookieModal.style.display = "none";

        // Se aceitou, carrega os scripts
        if (localStorage.getItem("cookies_choice") === "all") {
             loadAnalytics();
        }
    }

    // Aceitar todos
    acceptAllBtn.addEventListener("click", () => {
        localStorage.setItem("cookies_choice", "all");
        localStorage.setItem("cookies_analytics", "true");
        localStorage.setItem("cookies_marketing", "true");
        cookieModal.style.display = "none";

        loadAnalytics(); // Carrega GA depois do clique
    });

    // Rejeitar todos
    rejectAllBtn.addEventListener("click", () => {
        localStorage.setItem("cookies_choice", "none");
        localStorage.setItem("cookies_analytics", "false");
        localStorage.setItem("cookies_marketing", "false");
        cookieModal.style.display = "none";
    });

    // Função que carrega Google Analytics
    function loadAnalytics() {
        if (!document.getElementById("ga-script")) {
            let script2 = document.createElement("script");
            script2.innerHTML = `
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', 'G-0NT5HLTZ2J');
            `;
            document.head.appendChild(script2);


            let scriptHotjar = document.createElement("script");
            scriptHotjar.innerHTML = `
                (function(h,o,t,j,a,r){
                    h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
                    h._hjSettings={hjid:6518381,hjsv:6};
                    a=o.getElementsByTagName('head')[0];
                    r=o.createElement('script');r.async=1;
                    r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
                    a.appendChild(r);
                })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
                `;
            document.head.appendChild(scriptHotjar);
        }
    }
</script>