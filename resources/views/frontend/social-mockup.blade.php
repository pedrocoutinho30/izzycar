<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IzzyCar - Novo Website</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #111111;
            --accent-color: #6e0707;
            --secondary-color: #f8f9fa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #111111 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            overflow-x: hidden;
        }

        .mockup-container {
            max-width: 430px;
            width: 100%;
            perspective: 1500px;
        }

        .mockup-wrapper {
            background: #1a1a1a;
            border-radius: 50px;
            box-shadow: 0 50px 100px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            transform: rotateY(0deg) rotateX(0deg);
            transition: transform 0.6s ease;
            border: 12px solid #1a1a1a;
            position: relative;
            height: 932px;
            max-height: 90vh;
        }

        /* Notch do iPhone */
        .mockup-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 150px;
            height: 30px;
            background: #1a1a1a;
            border-radius: 0 0 20px 20px;
            z-index: 1000;
        }

        /* Content scroll container */
        .phone-content {
            height: 100%;
            overflow-y: scroll;
            overflow-x: hidden;
            scroll-behavior: smooth;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .phone-content::-webkit-scrollbar {
            display: none;
        }

        .mockup-wrapper:hover {
            transform: rotateY(0deg) rotateX(0deg) scale(1.02);
        }

        @keyframes autoScroll {
            0% {
                scroll-behavior: smooth;
            }
            20% {
                scroll-behavior: smooth;
            }
            80% {
                scroll-behavior: smooth;
            }
            100% {
                scroll-behavior: smooth;
            }
        }

        /* Mobile Status Bar */
        .mobile-status-bar {
            background: rgba(17, 17, 17, 0.95);
            padding: 0.5rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.75rem;
            color: #fff;
            position: relative;
            z-index: 999;
        }

        .status-time {
            font-weight: 600;
        }

        .status-icons {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .status-icons svg {
            width: 14px;
            height: 14px;
        }

        /* Hero Section Mockup */
        .hero-mockup {
            background: linear-gradient(135deg, #111111 0%, #1a1a1a 100%);
            padding: 3rem 1.5rem;
            position: relative;
            overflow: hidden;
            min-height: 600px;
            display: flex;
            align-items: center;
        }

        .hero-mockup::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><rect width="100" height="100" fill="none"/><path d="M0 0L50 50M50 50L100 0M50 50L100 100M50 50L0 100" stroke="rgba(255,255,255,0.02)" stroke-width="1"/></svg>');
            opacity: 0.3;
        }

        .navbar-mockup {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1.5rem;
            background: rgba(17, 17, 17, 0.95);
            position: relative;
            z-index: 10;
            border-bottom: 1px solid rgba(110, 7, 7, 0.2);
        }

        .hamburger-icon {
            width: 24px;
            height: 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-around;
        }

        .hamburger-icon span {
            display: block;
            width: 100%;
            height: 2px;
            background: #fff;
            border-radius: 2px;
        }

        .logo-mockup {
            height: 45px;
            filter: brightness(1.2);
        }

        .nav-items {
            display: none;
        }

        .hero-content-mockup {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 2rem 0;
        }

        .badge-mockup {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, rgba(110, 7, 7, 0.2) 0%, rgba(153, 0, 0, 0.2) 100%);
            color: #ff6b6b;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 2rem;
            border: 1px solid rgba(110, 7, 7, 0.3);
        }

        .hero-title-mockup {
            font-size: 2.2rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .text-gradient-mockup {
            background: linear-gradient(135deg, #ff6b6b 0%, #990000 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-description-mockup {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 2rem;
            max-width: 100%;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }

        .hero-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            justify-content: center;
        }

        .btn-primary-mockup {
            background: linear-gradient(135deg, #6e0707 0%, #990000 100%);
            color: #fff;
            padding: 0.85rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            border: none;
            box-shadow: 0 10px 30px rgba(110, 7, 7, 0.4);
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-secondary-mockup {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 0.85rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            border: 2px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            width: 100%;
        }

        /* Services Section */
        .services-mockup {
            padding: 2.5rem 1.5rem;
            background: #fff;
            min-height: 500px;
        }

        .section-title-mockup {
            text-align: center;
            font-size: 1.75rem;
            font-weight: 700;
            color: #111;
            margin-bottom: 2rem;
        }

        .services-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .service-card-mockup {
            background: #fff;
            border: 1px solid rgba(110, 7, 7, 0.1);
            border-radius: 15px;
            padding: 1.75rem;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .service-card-mockup:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(110, 7, 7, 0.15);
        }

        .service-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, rgba(110, 7, 7, 0.1) 0%, rgba(153, 0, 0, 0.1) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .service-icon svg {
            width: 30px;
            height: 30px;
            stroke: var(--accent-color);
        }

        .service-title-mockup {
            font-size: 1.3rem;
            font-weight: 600;
            color: #111;
            margin-bottom: 0.75rem;
        }

        .service-description-mockup {
            font-size: 0.95rem;
            color: #666;
            line-height: 1.6;
        }

        /* Announcement Badge */
        .announcement-badge {
            position: fixed;
            top: 2rem;
            right: 2rem;
            background: linear-gradient(135deg, #6e0707 0%, #990000 100%);
            color: #fff;
            padding: 1.5rem 2rem;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(110, 7, 7, 0.4);
            font-weight: 700;
            font-size: 1.2rem;
            z-index: 1000;
            animation: pulse 2s ease-in-out infinite;
            text-align: center;
        }

        .announcement-badge .emoji {
            font-size: 2rem;
            display: block;
            margin-bottom: 0.5rem;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .mockup-wrapper {
                border-radius: 20px;
            }

            .hero-title-mockup {
                font-size: 2rem;
            }

            .hero-description-mockup {
                font-size: 1rem;
            }

            .announcement-badge {
                top: 1rem;
                right: 1rem;
                left: 1rem;
                font-size: 1rem;
                padding: 1rem 1.5rem;
            }

            .nav-items {
                display: none;
            }

            .services-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Print Optimization */
        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .mockup-wrapper {
                box-shadow: none;
                transform: none;
                animation: none;
            }

            .announcement-badge {
                position: absolute;
            }
        }
    </style>
</head>
<body>

    <!-- Announcement Badge -->


    <div class="mockup-container">
        <div class="mockup-wrapper">
            <div class="phone-content" id="phoneContent">
            <!-- Mobile Status Bar -->
            <div class="mobile-status-bar">
                <div class="status-time">10:10</div>
                <div class="status-icons">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="1" y="5" width="22" height="14" rx="2" ry="2"></rect>
                        <line x1="23" y1="13" x2="23" y2="11"></line>
                    </svg>
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M1 9l2 2c4.97-4.97 13.03-4.97 18 0l2-2C16.93 2.93 7.08 2.93 1 9zm8 8l3 3 3-3c-1.65-1.66-4.34-1.66-6 0zm-4-4l2 2c2.76-2.76 7.24-2.76 10 0l2-2C15.14 9.14 8.87 9.14 5 13z"></path>
                    </svg>
                </div>
            </div>

            <!-- Navbar -->
            <div class="navbar-mockup">
                <div class="hamburger-icon">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <img src="{{ asset('img/logo-transparente.png') }}" alt="IzzyCar" class="logo-mockup">
                <div style="width: 24px;"></div>
            </div>

            <!-- Hero Section -->
            <div class="hero-mockup">
                <div class="hero-content-mockup">
                    <div class="badge-mockup">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                        </svg>
                        Importação Automóvel Chave na Mão
                    </div>
                    <h1 class="hero-title-mockup">
                        O Seu Carro dos Sonhos,<br>
                        <span class="text-gradient-mockup">Ao Melhor Preço</span>
                    </h1>
                    <p class="hero-description-mockup">
                        Especializados em importação de veículos de toda a Europa. Do início ao fim, cuidamos de cada detalhe.
                    </p>
                    <div class="hero-buttons">
                        <button class="btn-primary-mockup">Quero Importar →</button>
                        <button class="btn-secondary-mockup">€ Simular Custos</button>
                    </div>
                </div>
            </div>

            <!-- Services Section -->
            <div class="services-mockup">
                <h2 class="section-title-mockup">Como Podemos Ajudar</h2>
                <div class="services-grid">
                    <div class="service-card-mockup">
                        <div class="service-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                                <path d="M2 17l10 5 10-5M2 12l10 5 10-5"></path>
                            </svg>
                        </div>
                        <h3 class="service-title-mockup">Importação Chave na Mão</h3>
                        <p class="service-description-mockup">Tratamos de todo o processo. Recebe o seu carro pronto a conduzir.</p>
                    </div>

                    <div class="service-card-mockup">
                        <div class="service-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                            </svg>
                        </div>
                        <h3 class="service-title-mockup">Legalização</h3>
                        <p class="service-description-mockup">Cuidamos de toda a documentação e burocracia necessária.</p>
                    </div>

                   
                </div>
            </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-scroll realista
        function autoScroll() {
            const phoneContent = document.getElementById('phoneContent');
            const scrollHeight = phoneContent.scrollHeight - phoneContent.clientHeight;
            let currentScroll = 0;
            let direction = 1; // 1 para baixo, -1 para cima
            
            function scroll() {
                // Pausa no topo
                if (currentScroll === 0 && direction === 1) {
                    setTimeout(() => {
                        startScrolling();
                    }, 2000);
                    return;
                }
                
                // Pausa no fundo
                if (currentScroll >= scrollHeight && direction === 1) {
                    setTimeout(() => {
                        direction = -1;
                        startScrolling();
                    }, 3000);
                    return;
                }
                
                // Pausa quando volta ao topo
                if (currentScroll <= 0 && direction === -1) {
                    currentScroll = 0;
                    phoneContent.scrollTop = 0;
                    setTimeout(() => {
                        direction = 1;
                        startScrolling();
                    }, 2000);
                    return;
                }
                
                currentScroll += direction * 2;
                phoneContent.scrollTop = currentScroll;
                
                requestAnimationFrame(scroll);
            }
            
            function startScrolling() {
                scroll();
            }
            
            // Iniciar após 1 segundo
            setTimeout(() => {
                startScrolling();
            }, 1000);
        }
        
        // Iniciar quando a página carregar
        window.addEventListener('load', autoScroll);
    </script>

    <script>
        // Animation on scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        });

        document.querySelectorAll('.service-card-mockup').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.6s ease';
            observer.observe(card);
        });
    </script>
</body>
</html>
