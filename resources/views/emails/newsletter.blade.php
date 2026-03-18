<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $headline }}</title>
</head>
<body style="margin:0;padding:0;background:#f6f7fb;font-family:Arial, sans-serif;color:#111;">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f6f7fb;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" width="600" style="max-width:600px;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="background:#6e0707;color:#fff;padding:24px 32px;">
                            <h1 style="margin:0;font-size:22px;">{{ $headline }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 32px;">
                            <p style="margin:0 0 12px 0;font-size:16px;line-height:1.6;">{{ $intro }}</p>
                            <p style="margin:0 0 20px 0;font-size:14px;line-height:1.6;color:#444;">
                                Selecionámos oportunidades especiais para si. Veja os destaques abaixo.
                            </p>
                            <a href="{{ url('/') }}" style="display:inline-block;background:#6e0707;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:600;">
                                Visitar Izzycar
                            </a>
                        </td>
                    </tr>
                   
                    <tr>
                        <td style="padding:0 32px 28px 32px;">
                            @foreach ($offers as $car)
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:18px;border:1px solid #e6e8ee;border-radius:12px;overflow:hidden;">
                                    <tr>
                                        <td style="padding:0;">
                                            <img src="{{ $car['image'] }}" alt="{{ $car['brand'] }} {{ $car['model'] }}" width="100%" style="display:block;width:100%;height:auto;" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding:16px 18px;">
                                            <h3 style="margin:0 0 8px 0;font-size:16px;">{{ $car['brand'] }} - {{ $car['model'] }}</h3>
                                            <p style="margin:0 0 6px 0;font-size:13px;color:#444;">
                                                Ano: <strong>{{ $car['year'] }}</strong> &nbsp;|&nbsp; Kms: <strong>{{ $car['kms'] }} km</strong>
                                            </p>
                                            @if(!empty($car['combustivel']))
                                                <p style="margin:0 0 6px 0;font-size:13px;color:#444;">
                                                    Combustível: <strong>{{ $car['combustivel'] }}</strong>
                                                </p>
                                            @endif
                                            <p style="margin:0 0 6px 0;font-size:13px;color:#444;">
                                                Preço chave na mão: <strong style="color:#6e0707;">{{ $car['price'] }}</strong>
                                            </p>
                                            <p style="margin:0 0 6px 0;font-size:13px;color:#0f766e;">
                                                Poupança face ao mercado nacional: <strong>{{ $car['savings'] }}</strong>
                                            </p>
                                            @if(!empty($car['equipamentos']))
                                                <p style="margin:0;font-size:12px;color:#666;">
                                                    Equipamentos: {{ $car['equipamentos'] }}
                                                </p>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 32px;background:#f3f4f6;color:#666;font-size:12px;text-align:center;">
                            Recebeste este email porque subscreveste a newsletter da Izzycar.
                            @if(isset($email))
                                <br>
                                <a href="{{ route('newsletter.unsubscribe') }}?email={{ urlencode($email) }}&name={{ urlencode($name ?? '') }}" style="color:#6e0707;text-decoration:underline;">
                                    Deixar de subscrever
                                </a>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
