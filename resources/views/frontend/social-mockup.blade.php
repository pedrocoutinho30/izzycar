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
            max-width: 1400px;
            width: 100%;
            perspective: 1500px;
        }

        .mockup-wrapper {
            background: #fff;
            border-radius: 30px;
            box-shadow: 0 50px 100px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            transform: rotateY(-5deg) rotateX(5deg);
            transition: transform 0.6s ease;
            animation: float 6s ease-in-out infinite;
        }

        .mockup-wrapper:hover {
            transform: rotateY(0deg) rotateX(0deg) scale(1.02);
        }

        @keyframes float {
            0%, 100% {
                transform: rotateY(-5deg) rotateX(5deg) translateY(0px);
            }
            50% {
                transform: rotateY(-5deg) rotateX(5deg) translateY(-20px);
            }
        }

        /* Browser Chrome */
        .browser-chrome {
            background: #e8e8e8;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid #ddd;
        }

        .browser-dots {
            display: flex;
            gap: 0.5rem;
        }

        .browser-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .dot-red { background: #ff5f56; }
        .dot-yellow { background: #ffbd2e; }
        .dot-green { background: #27c93f; }

        .browser-url {
            flex: 1;
            background: #fff;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            color: #666;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .browser-url svg {
            width: 16px;
            height: 16px;
            color: #27c93f;
        }

        /* Hero Section Mockup */
        .hero-mockup {
            background: linear-gradient(135deg, #111111 0%, #1a1a1a 100%);
            padding: 2.5rem 2rem;
            position: relative;
            overflow: hidden;
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
            padding: 1rem 2rem;
            background: rgba(17, 17, 17, 0.95);
            position: relative;
            z-index: 10;
            border-bottom: 1px solid rgba(110, 7, 7, 0.2);
        }

        .logo-mockup {
            height: 70px;
            filter: brightness(1.2);
        }

        .nav-items {
            display: flex;
            gap: 2rem;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
            font-weight: 600;
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
            font-size: 2.8rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 1.2rem;
            line-height: 1.2;
        }

        .text-gradient-mockup {
            background: linear-gradient(135deg, #ff6b6b 0%, #990000 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-description-mockup {
            font-size: 1.05rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 1.8rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary-mockup {
            background: linear-gradient(135deg, #6e0707 0%, #990000 100%);
            color: #fff;
            padding: 0.85rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            border: none;
            box-shadow: 0 10px 30px rgba(110, 7, 7, 0.4);
            transition: all 0.3s ease;
        }

        .btn-secondary-mockup {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 0.85rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            border: 2px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }

        /* Services Section */
        .services-mockup {
            padding: 2rem;
            background: #fff;
        }

        .section-title-mockup {
            text-align: center;
            font-size: 2rem;
            font-weight: 700;
            color: #111;
            margin-bottom: 2rem;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .service-card-mockup {
            background: #fff;
            border: 1px solid rgba(110, 7, 7, 0.1);
            border-radius: 20px;
            padding: 1.5rem;
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
    <div class="announcement-badge">
        <span class="emoji">🎉</span>
        NOVO WEBSITE<br>
    </div>

    <div class="mockup-container">
        <div class="mockup-wrapper">
            <!-- Browser Chrome -->
            <div class="browser-chrome">
                <div class="browser-dots">
                    <div class="browser-dot dot-red"></div>
                    <div class="browser-dot dot-yellow"></div>
                    <div class="browser-dot dot-green"></div>
                </div>
                <div class="browser-url">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="9" y="11" width="6" height="11" rx="2"></rect>
                        <path d="M12 11V7a2 2 0 0 1 2-2h2"></path>
                        <circle cx="12" cy="12" r="10"></circle>
                    </svg>
                    www.izzycar.pt
                </div>
            </div>

            <!-- Navbar -->
            <div class="navbar-mockup">
                <img src="{{ asset('img/logo-izzycar-branco.png') }}" alt="IzzyCar" class="logo-mockup">
                <div class="nav-items">
                    <span>Início</span>
                    <span>Importação</span>
                    <span>Viaturas</span>
                    <span>Legalização</span>
                    <span>Contacto</span>
                </div>
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
