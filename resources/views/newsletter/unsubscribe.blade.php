<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="4;url={{ url('/') }}">
    <title>Pedido recebido</title>
</head>
<body style="margin:0;padding:0;background:#f6f7fb;font-family:Arial, sans-serif;color:#111;">
    <div style="max-width:640px;margin:60px auto;background:#fff;border-radius:12px;padding:32px;box-shadow:0 10px 30px rgba(0,0,0,0.08);text-align:center;">
        <h1 style="margin:0 0 12px 0;font-size:22px;color:#6e0707;">Pedido recebido</h1>
        <p style="margin:0 0 8px 0;font-size:16px;line-height:1.6;">
            Pedido recebido. Vamos tratar do cancelamento.
        </p>
        <p style="margin:0;font-size:14px;color:#666;">
            Vai ser redirecionado para o site em 4 segundos.
        </p>
    </div>
    <script>
        setTimeout(function () {
            window.location.href = "{{ url('/') }}";
        }, 4000);
    </script>
</body>
</html>
