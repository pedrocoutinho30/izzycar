<!DOCTYPE html>
<html lang="pt">
<head><meta charset="UTF-8"><title>Confirme a subscrição</title></head>
<body style="margin:0;padding:24px;background:#f6f7fb;font-family:Arial,sans-serif;color:#111;">
    <div style="max-width:560px;margin:0 auto;background:#fff;border-radius:12px;padding:28px;">
        <h1 style="margin:0 0 12px 0;font-size:20px;color:#6e0707;">Confirme a sua subscrição</h1>
        <p style="font-size:15px;line-height:1.6;">Olá{{ $recipientName !== '' ? ' ' . $recipientName : '' }}, recebemos um pedido para subscrever a newsletter da Izzycar com este email.</p>
        <p style="margin:24px 0;"><a href="{{ $confirmUrl }}" style="background:#92642D;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none;">Confirmar subscrição</a></p>
        <p style="font-size:13px;color:#666;line-height:1.5;">O link é válido durante 7 dias. Se não fez este pedido, ignore este email e não receberá nada.</p>
    </div>
</body>
</html>
