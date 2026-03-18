<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancelamento de Newsletter</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #990000 0%, #6e0707 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #990000;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box strong {
            color: #990000;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ Cancelamento de Newsletter</h1>
        </div>
        
        <div class="content">
            <p>Um cliente cancelou a subscrição da newsletter.</p>
            
            <div class="info-box">
                <p><strong>Email:</strong> {{ $clientEmail }}</p>
                <p><strong>Nome:</strong> {{ $clientName ?? 'N/A' }}</p>
                <p><strong>Data do Cancelamento:</strong> {{ $unsubscribeDate }}</p>
            </div>
            
            <p>O campo <code>newsletter_consent</code> foi automaticamente atualizado para <strong>false</strong> na base de dados.</p>
        </div>
        
        <div class="footer">
            <p>Esta é uma notificação automática do sistema Izzycar</p>
            <p>© {{ date('Y') }} Izzycar - Importação automóvel</p>
        </div>
    </div>
</body>
</html>
