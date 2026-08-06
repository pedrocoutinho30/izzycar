<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Candidatura a Angariador - Izzycar</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css">

    <style>
        :root {
            --admin-primary: #6e0707;
            --admin-primary-dark: #4a0505;
            --border-radius: 12px;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .register-container { width: 100%; max-width: 640px; }

        .register-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .register-header {
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-primary-dark));
            padding: 2rem;
            text-align: center;
            color: #fff;
        }
        .register-header img { max-height: 60px; margin-bottom: 1rem; filter: brightness(0) invert(1); }
        .register-header h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: .35rem; }
        .register-header p { font-size: .95rem; opacity: .9; margin: 0; }

        .register-body { padding: 2rem; }

        .form-label { font-weight: 600; color: #333; margin-bottom: 0.4rem; font-size: 0.88rem; }
        .form-control {
            padding: 0.7rem 1rem;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 0.92rem;
            transition: var(--transition);
        }
        .form-control:focus {
            outline: none;
            border-color: var(--admin-primary);
            box-shadow: 0 0 0 3px rgba(110, 7, 7, 0.1);
        }

        .btn-register {
            width: 100%;
            padding: 0.8rem;
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-primary-dark));
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
        }
        .btn-register:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); color: #fff; }

        .alert { border-radius: 8px; border: none; padding: 1rem; margin-bottom: 1.5rem; }

        .manual-link { text-align: center; margin-top: 1.5rem; font-size: .85rem; }
        .manual-link a { color: var(--admin-primary); text-decoration: none; font-weight: 600; }
        .manual-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <img src="{{ asset('img/logo_final.png') }}" alt="Izzycar Logo">
                <h1>Candidatura a Angariador</h1>
                <p>Junte-se ao programa de angariadores Izzycar</p>
            </div>

            <div class="register-body">
                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('public.angariador.register.store') }}">
                    @csrf

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nome <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apelido <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telemóvel <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="9XX XXX XXX" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Localização</label>
                        <input type="text" name="location" class="form-control" value="{{ old('location') }}" placeholder="ex: Porto">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">NIF <span class="text-muted">(opcional)</span></label>
                            <input type="text" name="nif" class="form-control" value="{{ old('nif') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">IBAN <span class="text-muted">(opcional)</span></label>
                            <input type="text" name="iban" class="form-control" value="{{ old('iban') }}" placeholder="PT50...">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Mensagem <span class="text-muted">(opcional)</span></label>
                        <textarea name="message" class="form-control" rows="3" placeholder="Fale-nos um pouco sobre si e porque quer ser angariador Izzycar">{{ old('message') }}</textarea>
                    </div>

                    <button type="submit" class="btn-register">
                        <i class="bi bi-send me-2"></i> Enviar Candidatura
                    </button>
                </form>

                <div class="manual-link">
                    Ainda não sabe como funciona? <a href="{{ route('public.manual.angariador') }}">Leia o manual do angariador</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
