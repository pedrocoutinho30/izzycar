<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>{{ $titulo }}</title>
</head>
<body style="margin:0;padding:0;background:#f6f7fb;font-family:Arial, sans-serif;color:#111;">
    <div style="max-width:640px;margin:60px auto;background:#fff;border-radius:12px;padding:32px;box-shadow:0 10px 30px rgba(0,0,0,0.08);text-align:center;">
        <h1 style="margin:0 0 12px 0;font-size:22px;color:#6e0707;">{{ $titulo }}</h1>
        <p style="margin:0 0 8px 0;font-size:16px;line-height:1.6;">{{ $mensagem }}</p>
        <p style="margin:16px 0 0 0;font-size:14px;"><a href="{{ url('/') }}" style="color:#92642D;">Voltar ao site</a></p>
    </div>
</body>
</html>
