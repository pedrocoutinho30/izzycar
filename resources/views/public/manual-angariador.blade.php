<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual do Angariador - Izzycar</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css">

    <style>
        :root {
            --admin-primary: #6e0707;
            --admin-primary-dark: #4a0505;
            --admin-secondary: #111111;
            --admin-light: #f8f9fa;
            --admin-border: #dee2e6;
            --border-radius: 12px;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.12);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f7fa;
            color: var(--admin-secondary);
            margin: 0;
        }

        .public-header {
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-primary-dark));
            color: #fff;
            padding: 2rem 1.5rem;
            text-align: center;
        }
        .public-header img { max-height: 60px; margin-bottom: .75rem; filter: brightness(0) invert(1); }
        .public-header h1 { font-size: 1.6rem; font-weight: 700; margin: 0 0 .25rem; }
        .public-header p { margin: 0; opacity: .9; font-size: .95rem; }

        .public-container { max-width: 1200px; margin: 0 auto; padding: 2rem 1.5rem 4rem; }

        /* Cards modernos (mesmo estilo do backoffice) */
        .modern-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: var(--transition);
            border: 1px solid transparent;
        }
        .modern-card:hover { box-shadow: var(--shadow-md); border-color: var(--admin-border); }
        .modern-card-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 1.5rem; padding-bottom: 1rem;
            border-bottom: 2px solid var(--admin-light);
        }
        .modern-card-title {
            font-size: 1.25rem; font-weight: 600; color: var(--admin-secondary);
            display: flex; align-items: center; gap: 0.75rem;
        }
        .modern-card-title i { color: var(--admin-primary); }

        .btn-primary-modern {
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-primary-dark));
            color: white; box-shadow: var(--shadow-sm); border: none;
        }
        .btn-primary-modern:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); color: white; }
    </style>
</head>
<body>

    <div class="public-header">
        <img src="{{ asset('img/logo_final.png') }}" alt="Izzycar Logo">
        <h1>Manual do Angariador</h1>
        <p>Guia completo do programa de angariadores Izzycar</p>
    </div>

    <div class="public-container">
        @include('admin.v2.manual._angariador-content', ['publicView' => true])
    </div>

    {{-- @stack('styles') fica aqui (depois do @include) porque o @push da
         partial só é registado quando o @include é processado — colocá-lo
         no <head>, antes do @include, renderizaria sempre vazio. --}}
    @stack('styles')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
