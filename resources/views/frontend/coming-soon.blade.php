<!DOCTYPE html>
<html lang="en">

<head>
    <script>
        (function(h, o, t, j, a, r) {
            h.hj = h.hj || function() {
                (h.hj.q = h.hj.q || []).push(arguments)
            };
            h._hjSettings = {
                hjid: 6518381,
                hjsv: 6
            };
            a = o.getElementsByTagName('head')[0];
            r = o.createElement('script');
            r.async = 1;
            r.src = t + h._hjSettings.hjid + j + h._hjSettings.hjsv;
            a.appendChild(r);
        })(window, document, 'https://static.hotjar.com/c/hotjar-', '.js?sv=');
    </script>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-0NT5HLTZ2J"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-0NT5HLTZ2J');
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IzzyCar - Compre o seu carro com facilidade e segurança</title>
    <meta name="description" content="IzzyCar - Compre o seu carro com facilidade e segurança. Rápido, prático e seguro, sem complicações.">

    <style>
        /* Reset e estilos básicos */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            color: #9a6c32;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            max-height: 100vh;
            background-size: cover;
        }

        /* Container principal para centralizar conteúdo */
        .container {
            background-color: black;
            text-align: center;
            padding: 0px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            max-height: 100%;
            /* Para evitar que o conteúdo ultrapasse a tela */
            /* Adiciona scroll se necessário, mas só dentro do container */
        }

        /* Logo responsivo */
        .logo {
            width: 100%;
            /* O logo ocupa 80% da largura do container */
            max-width: 450px;
            /* Limita o tamanho máximo */
            height: auto;
            margin-bottom: 20px;
        }

        h2 {
            font-size: 2em;
            margin: 20px 0;
            color: #9a6c32;
        }

        .contact-title {
            font-size: 1.3em;
            margin-top: 100px;
            color: #9a6c32;
        }

        /* Ícones de contato */
        .contact-icons {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            margin: 20px 0;
        }

        .contact-icons a {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            font-size: 1em;
            color: dimgrey;
            transition: color 0.3s ease;
        }

        .contact-icons a:hover {
            color: #9a6c32;
        }

        .contact-icons svg {
            color: #9a6c32;
            width: 25px;
            height: 25px;
        }

        /* Ícone social */
        .social-link {
            color: #9a6c32;
            display: inline-block;
            margin-top: 10px;
        }

        .social-link svg {
            width: 40px;
            height: 40px;
            transition: fill 0.3s ease;
        }

        .social-link:hover svg {
            fill: #9a6c32;
            color: black;
        }

        /* Footer fixado */
        .footer {
            text-align: center;
            font-size: 0.9em;
            color: #fff;
            padding: 10px 0;
            background-color: rgba(0, 0, 0, 0.8);
            position: relative;
        }

        /* Media Queries para diferentes tamanhos de tela */
        @media (max-width: 768px) {
            h2 {
                font-size: 1.8em;
            }

            .contact-title {
                font-size: 1.1em;
            }

            .contact-icons a {
                font-size: 0.9em;
            }

            .logo {
                width: 90%;
                /* O logo ocupa mais espaço em telas menores */
            }
        }

        @media (max-width: 480px) {
            h2 {
                font-size: 1.5em;
            }

            .contact-title {
                font-size: 1em;
            }

            .contact-icons a {
                font-size: 0.8em;
            }

            .logo {
                width: 100%;
                /* O logo ocupa toda a largura do container */
            }

            .social-link svg {
                width: 35px;
                height: 35px;
            }


        }
    </style>
</head>

<body>
    <div class="container">
        <img src="{{ asset($logotipo) }}" class="logo" alt="Logo da izzycar">
        <h2>Brevemente disponível</h2>
        <h3>Estamos a trabalhar para melhorar a sua experiência</h3>
        <h3>Em breve teremos novidades</h3>
        <p class="contact-title">CONTACTOS</p>
        <div class="contact-icons">
            <a href="tel:+351928459346" title="Ligar">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path
                        d="M22 16.92v3a2 2 0 0 1-2.18 2 19.86 19.86 0 0 1-8.63-3.14A19.51 19.51 0 0 1 3.11 9.8 19.86 19.86 0 0 1 0 1.37 2 2 0 0 1 2 0h3a2 2 0 0 1 2 1.72 12.44 12.44 0 0 0 .66 2.83 2 2 0 0 1-.45 2.11L5.91 8a16 16 0 0 0 6.09 6.09l1.32-1.32a2 2 0 0 1 2.11-.45 12.44 12.44 0 0 0 2.83.66A2 2 0 0 1 22 16.92z">
                    </path>
                </svg>
                +351 928 459 346
            </a>
            <a href="mailto:izzycarpt@gmail" title="Enviar E-mail">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M22 6a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h20z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
                izzycarpt@gmail
            </a>
        </div>
        <div class="flex space-x-4">
            <a href="https://www.instagram.com/izzycarpt/" target="_blank" rel="noopener noreferrer" class="social-link">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                    <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                    <path d="M16 11.37a4 4 0 1 1-4.73-4.73"></path>
                    <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                </svg>
            </a>
            <a href="https://www.facebook.com/profile.php?id=61572831810539" target="_blank" rel="noopener noreferrer" class="social-link">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                    <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                </svg>
            </a>
        </div>
        <p>© 2025 IzzyCar. Todos os direitos reservados.</p>
    </div>

</body>

</html>