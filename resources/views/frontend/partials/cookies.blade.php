<!-- Modal Cookies -->
<div id="cookieModal" class="cookie-modal">
    <div class="cookie-content card news-listing shadow-lg">
        <h3>Este site usa cookies</h3>
        <p class="text-dark">
            Utilizamos cookies para melhorar a sua experiência de navegação, apresentar anúncios ou conteúdos personalizados e analisar o nosso tráfego.
            Ao clicar em "Aceitar Todos", concorda com a utilização de cookies.
        </p>

        <div class="cookie-buttons">
            <button id="acceptAll">Aceitar Todos</button>
            <button id="rejectAll">Rejeitar</button>
        </div>
    </div>
</div>

<style>
    /* Modal principal */
    .cookie-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(5px);
        /* Aplica blur no resto da página */
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    /* Conteúdo da modal */
    .cookie-content {
        background: #fff;
        padding: 25px;
        max-width: 400px;
        width: 90%;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .cookie-buttons {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 15px;
    }

    .cookie-buttons button {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
        font-size: 14px;
    }

    #acceptAll {
        background: #28a745;
        color: white;
    }

    #rejectAll {
        background: #dc3545;
        color: white;
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