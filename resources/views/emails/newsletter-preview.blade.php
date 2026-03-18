<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $newsletter->title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #111111 0%, #1a1a1a 50%, #111111 100%);
            background-attachment: fixed;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #990000 0%, #6e0707 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.3;
        }

        .logo {
            position: absolute;
            top: 20px;
            left: 30px;
            z-index: 2;
        }

        .logo img {
            height: 40px;
            width: auto;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: bold;
            position: relative;
            z-index: 1;
            padding-left: 100px;
        }

        .header p {
            font-size: 16px;
            opacity: 0.95;
            position: relative;
            z-index: 1;
        }

        /* Content */
        .content {
            padding: 40px 30px;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        }

        .text-content {
            font-size: 15px;
            line-height: 1.8;
            color: #333333;
            margin-bottom: 40px;
            text-align: center;
        }

        .offers-title {
            font-size: 26px;
            color: #990000;
            margin-bottom: 30px;
            text-align: center;
            font-weight: bold;
            position: relative;
            padding-bottom: 15px;
        }

        .offers-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(135deg, #990000 0%, #6e0707 100%);
        }

        /* Offer Card */
        .offer {
            background-color: #ffffff;
            border-radius: 12px;
            margin-bottom: 25px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid #e0e0e0;
            transition: transform 0.3s ease;
        }

        .offer-image {
            width: 100%;
            height: 280px;
            object-fit: cover;
            display: block;
        }

        .offer-details {
            padding: 25px;
        }

        .offer-title {
            font-size: 22px;
            color: #111;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .offer-subtitle {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .offer-price-section {
            background: rgba(110, 7, 7, 0.05);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #990000;
        }

        .offer-price {
            font-size: 16px;
            color: #990000;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .offer-savings {
            font-size: 28px;
            color: #28a745;
            font-weight: bold;
        }

        .offer-info {
            font-size: 14px;
            color: #666666;
            line-height: 1.6;
            background: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
        }

        .offer-info strong {
            color: #990000;
        }

        .offer-cta {
            margin-top: 15px;
            text-align: center;
        }

        .offer-cta a {
            display: inline-block;
            background: linear-gradient(135deg, #990000 0%, #6e0707 100%);
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 14px;
            transition: transform 0.2s ease;
        }

        .offer-cta a:hover {
            transform: translateY(-2px);
        }

        /* Info Blocks */
        .info-blocks {
            background: #ffffff;
            padding: 40px 30px;
            border-top: 1px solid #e0e0e0;
        }

        .info-blocks-title {
            font-size: 24px;
            color: #990000;
            margin-bottom: 30px;
            text-align: center;
            font-weight: bold;
            position: relative;
            padding-bottom: 15px;
        }

        .info-blocks-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(135deg, #990000 0%, #6e0707 100%);
        }

        .info-block {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-left: 4px solid #990000;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .info-block-title {
            font-size: 16px;
            font-weight: bold;
            color: #990000;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-block-icon {
            font-size: 20px;
        }

        .info-block-text {
            font-size: 14px;
            color: #333;
            line-height: 1.7;
        }

        .info-block-highlight {
            background: rgba(110, 7, 7, 0.05);
            padding: 10px 15px;
            border-radius: 6px;
            margin-top: 10px;
            font-size: 13px;
            color: #6e0707;
            font-weight: 500;
        }

        /* Features */
        .features {
            background: #ffffff;
            padding: 30px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .feature {
            text-align: center;
            padding: 15px;
        }

        .feature-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .feature-title {
            font-size: 14px;
            font-weight: bold;
            color: #990000;
            margin-bottom: 5px;
        }

        .feature-text {
            font-size: 12px;
            color: #6c757d;
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, #111111 0%, #1a1a1a 100%);
            color: rgba(255, 255, 255, 0.8);
            padding: 30px;
            text-align: center;
        }

        .footer-logo {
            font-size: 24px;
            font-weight: bold;
            color: white;
            margin-bottom: 15px;
        }

        .footer-text {
            font-size: 13px;
            line-height: 1.8;
            margin-bottom: 20px;
        }

        .footer-contact {
            margin-bottom: 20px;
        }

        .footer-contact a {
            color: #990000;
            text-decoration: none;
            display: inline-block;
            margin: 5px 10px;
            font-weight: bold;
        }

        .footer-links {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-size: 12px;
            margin: 0 10px;
        }

        .footer-links a:hover {
            color: #990000;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #990000 0%, #6e0707 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .cta-title {
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .cta-text {
            font-size: 16px;
            margin-bottom: 25px;
            opacity: 0.95;
        }

        .cta-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .cta-button {
            display: inline-block;
            background: white;
            color: #990000;
            text-decoration: none;
            padding: 15px 35px;
            border-radius: 30px;
            font-weight: bold;
            font-size: 16px;
            transition: transform 0.2s ease;
        }

        .cta-button:hover {
            transform: translateY(-2px);
        }

        .cta-button-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        @media only screen and (max-width: 600px) {
            body {
                padding: 10px;
            }

            .header h1 {
                font-size: 24px;
            }

            .logo {
                font-size: 28px;
            }

            .content {
                padding: 30px 20px;
            }

            .offer-image {
                height: 200px;
            }

            .offer-details {
                padding: 20px;
            }

            .info-blocks {
                padding: 30px 20px;
            }

            .info-block {
                padding: 15px;
            }

            .info-block-title {
                font-size: 15px;
            }

            .cta-section {
                padding: 30px 20px;
            }

            .cta-title {
                font-size: 22px;
            }

            .cta-text {
                font-size: 15px;
            }

            .cta-buttons {
                flex-direction: column;
                gap: 12px;
            }

            .cta-button {
                padding: 14px 30px;
                font-size: 15px;
                width: 100%;
            }

            .offer-cta a {
                padding: 10px 25px;
                font-size: 13px;
            }

            .features {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .footer {
                padding: 25px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo"><img src="{{ asset('path/to/logo.png') }}" alt="IZZYCAR Logo"></div>

        </div>

        <!-- Content -->
        <div class="content">

            @if($newsletter->offers->count() > 0)


            <h2 class="offers-title">Recomendações da semana</h2>

            @foreach($newsletter->offers as $offer)
            @if($offer->is_active)
            <div class="offer">
                @if($offer->image)
                <img src="{{ url('storage/' . $offer->image) }}" alt="{{ $offer->brand }} {{ $offer->model }}" class="offer-image">
                @endif

                <div class="offer-details">
                    <div class="offer-title">{{ $offer->brand }} {{ $offer->model }}</div>
                    <div class="offer-subtitle">
                        🗓️ {{ $offer->year }} • 🚗 {{ number_format($offer->kms, 0, ',', '.') }} km @if($offer->combustivel) • ⛽ {{ $offer->combustivel }} @endif
                    </div>

                    <div class="offer-price-section">
                        <div class="offer-price">Preço Chave na mão: {{ number_format($offer->price, 0, ',', '.') }}€</div>
                        <div class="offer-savings">💰 Poupança {{ number_format($offer->savings, 0, ',', '.') }}€</div>
                    </div>

                    @if($offer->equipamentos)
                    <div class="offer-info">
                        <strong>✨ Equipamento:</strong><br>
                        {{ $offer->equipamentos }}
                    </div>
                    @endif

                    <div class="offer-cta">
                        <a href="https://wa.me/351928459346?text=Olá!%20Tenho%20interesse%20no%20{{ urlencode($offer->brand . ' ' . $offer->model) }}">
                            💬 Quero mais informações
                        </a>
                    </div>
                </div>
            </div>
            @endif
            @endforeach
            @endif
        </div>
        <!-- Features -->
        <div class="features">
            <div class="feature">
                <div class="feature-icon">🔒</div>
                <div class="feature-title">100% Seguro</div>
                <div class="feature-text">Processo transparente e garantido</div>
            </div>
            <div class="feature">
                <div class="feature-icon">⚡</div>
                <div class="feature-title">Entrega Rápida</div>
                <div class="feature-text">3-6 semanas em média</div>
            </div>
            <div class="feature">
                <div class="feature-icon">💶</div>
                <div class="feature-title">Melhor Preço</div>
                <div class="feature-text">Economize até 30%</div>
            </div>
            <div class="feature">
                <div class="feature-icon">🎯</div>
                <div class="feature-title">Suporte Total</div>
                <div class="feature-text">Do início ao fim</div>
            </div>
        </div>

        <!-- Info Blocks -->
        <div class="info-blocks">
            <h2 class="info-blocks-title">Porquê escolher a Izzycar?</h2>

            <div class="info-block">
                <div class="info-block-title">
                    <span class="info-block-icon">🔍</span>
                    Análise Rigorosa
                </div>
                <div class="info-block-text">
                    Todas as viaturas apresentadas são previamente analisadas, com base em histórico, quilometragem, estado geral e enquadramento de preço no mercado europeu.
                    <div class="info-block-highlight">
                        O objetivo não é encontrar o mais barato, mas sim o melhor negócio possível.
                    </div>
                </div>
            </div>

            <div class="info-block">
                <div class="info-block-title">
                    <span class="info-block-icon">🎯</span>
                    Acompanhamento Completo
                </div>
                <div class="info-block-text">
                    O serviço inclui acompanhamento completo: pesquisa, validação da viatura, negociação, transporte, inspeção e legalização em Portugal.
                    <div class="info-block-highlight">
                        O cliente recebe sempre o valor final chave-na-mão, sem surpresas.
                    </div>
                </div>
            </div>

            <div class="info-block">
                <div class="info-block-title">
                    <span class="info-block-icon">💎</span>
                    Transparência Total
                </div>
                <div class="info-block-text">
                    Os valores apresentados já incluem todos os custos associados ao processo de importação, incluindo ISV, transporte e legalização.
                    <div class="info-block-highlight">
                        Sempre que aplicável, o IUC também é considerado no valor final.
                    </div>
                </div>
            </div>

            <div class="info-block">
                <div class="info-block-title">
                    <span class="info-block-icon">✅</span>
                    Critério de Seleção
                </div>
                <div class="info-block-text">
                    Nem todas as viaturas compensam importar.
                    <div class="info-block-highlight">
                        As sugestões enviadas refletem apenas oportunidades que fazem sentido em termos de preço, estado e histórico.
                    </div>
                </div>
            </div>

            <div class="info-block">
                <div class="info-block-title">
                    <span class="info-block-icon">⏰</span>
                    Oportunidades Dinâmicas
                </div>
                <div class="info-block-text">
                    As oportunidades apresentadas são dinâmicas e podem deixar de estar disponíveis sem aviso prévio, dado o ritmo do mercado europeu.
                    <div class="info-block-highlight">
                        Se algo lhe interessar, contacte-nos o mais rapidamente possível.
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="cta-section">
            <h2 class="cta-title">Pronto para importar o seu próximo carro?</h2>
            <p class="cta-text">Entre em contacto connosco e receba uma proposta personalizada sem compromisso</p>
            <div class="cta-buttons">
                <a href="https://wa.me/351928459346?text=Olá!%20Gostaria%20de%20saber%20mais%20sobre%20importação%20de%20veículos" class="cta-button">
                    💬 Falar no WhatsApp
                </a>
                <a href="https://izzycar.pt/formulario-importacao" class="cta-button cta-button-secondary">
                    📝 Pedir Proposta
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-logo">IZZYCAR</div>
            <div class="footer-text">
                Importação de veículos de qualidade com transparência e confiança
            </div>

            <div class="footer-contact">
                📞 <a href="https://wa.me/351928459346">+351 928 459 346</a><br>
                ✉️ <a href="mailto:geral@izzycar.pt">geral@izzycar.pt</a>
            </div>

            <div class="footer-links">
                <a href="https://izzycar.pt">Home</a> •
                <a href="https://izzycar.pt/importacao">Importação</a> •
                <a href="https://izzycar.pt/legalizacao">Legalização</a> •
                <a href="{{ route('newsletter.unsubscribe') }}?email={{ $previewClient['email']  }}&name={{ $previewClient['name']  }}">Cancelar subscrição</a>
            </div>

            <div style="margin-top: 20px; font-size: 12px; color: rgba(255,255,255,0.5);">
                © {{ date('Y') }} Izzycar. Todos os direitos reservados.
            </div>
        </div>
    </div>
</body>

</html>