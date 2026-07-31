<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('legalization.email_heading') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; color: #333; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #111111 0%, #2a0000 100%); padding: 40px 30px; text-align: center; }
        .header img { width: 150px; max-width: 150px; height: auto; margin-bottom: 16px; }
        .header h1 { color: #ffffff; font-size: 22px; font-weight: bold; }
        .header p { color: rgba(255,255,255,0.75); font-size: 14px; margin-top: 6px; }
        .body { padding: 36px 30px; }
        .greeting { font-size: 18px; font-weight: bold; color: #111; margin-bottom: 10px; }
        .intro { font-size: 15px; color: #555; line-height: 1.7; margin-bottom: 28px; }
        .cta-block { text-align: center; margin: 32px 0; }
        .cta-btn { display: inline-block; background: linear-gradient(135deg, #990000, #6e0707); color: #ffffff !important; text-decoration: none; padding: 16px 36px; border-radius: 8px; font-size: 16px; font-weight: bold; letter-spacing: 0.5px; }
        .note { font-size: 13px; color: #888; text-align: center; margin-top: 8px; }
        .divider { border: none; border-top: 1px solid #eee; margin: 28px 0; }
        .secondary-cta { text-align: center; font-size: 14px; color: #555; margin-bottom: 8px; }
        .secondary-cta a { color: #990000; font-weight: bold; text-decoration: none; }
        .footer { background: #111111; padding: 28px 30px; text-align: center; }
        .footer img { width: 90px; max-width: 90px; height: auto; margin-bottom: 12px; }
        .footer p { color: rgba(255,255,255,0.5); font-size: 12px; line-height: 1.8; }
        @media only screen and (max-width: 600px) {
            .wrapper { margin: 0; border-radius: 0; }
            .body { padding: 24px 20px; }
            .cta-btn { padding: 14px 24px; font-size: 15px; }
        }
    </style>
</head>
<body>
<div class="wrapper">

    <div class="header">
        <img src="https://izzycar.pt/storage/settings/logo.png" alt="Izzycar" width="150" style="width:150px;max-width:150px;height:auto;">
        <h1>{{ __('legalization.email_heading') }}</h1>
        <p>{{ $legalization->marca }} {{ $legalization->modelo }}</p>
    </div>

    <div class="body">
        <p class="greeting">{{ __('legalization.email_greeting', ['name' => $clientName]) }}</p>
        <p class="intro">
            {{ __('legalization.email_intro') }}
        </p>

        <div class="cta-block">
            <a href="{{ $trackingUrl }}" class="cta-btn">{{ __('legalization.email_cta') }}</a>
            <p class="note">{{ __('legalization.email_note') }}</p>
        </div>

        <hr class="divider">

        <div class="secondary-cta">
            {{ __('legalization.email_question') }} <a href="https://izzycar.pt/contactos">{{ __('legalization.email_contact') }}</a>
        </div>
    </div>

    <div class="footer">
        <img src="https://izzycar.pt/storage/settings/logo_redondo.png" alt="Izzycar" width="90" style="width:90px;max-width:90px;height:auto;">
        <p>
            {{ __('legalization.email_tagline') }}<br>
            <a href="https://izzycar.pt" style="color: rgba(255,255,255,0.5);">izzycar.pt</a><br><br>
            {{ __('legalization.email_disclaimer') }}
        </p>
    </div>

</div>
</body>
</html>
