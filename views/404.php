<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página no encontrada | UNIRED</title>
    <link rel="stylesheet" href="<?php echo asset('assets/styles/styles.css'); ?>">
    <script>
        (function() {
            const savedTheme = localStorage.getItem("unired_theme");
            if (savedTheme === "dark") {
                document.documentElement.setAttribute("data-theme", "dark");
            }
        })();
    </script>
    <style>
        .error-page-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background-color: #f0f4f8;
            background-image: url("/assets/images/fondoDiamante.png");
            background-size: cover;
            background-position: center;
            font-family: 'Roboto', sans-serif;
        }

        html[data-theme="dark"] .error-page-container {
            background-color: #0b1120;
            background-image: none;
        }

        .error-card {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 24px;
            padding: 50px 40px;
            max-width: 520px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.12);
        }

        html[data-theme="dark"] .error-card {
            background: rgba(30, 41, 59, 0.96);
            border-color: rgba(51, 65, 85, 0.85);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
            color: #f8fafc;
        }

        .error-logo {
            max-width: 180px;
            margin-bottom: 25px;
        }

        .error-code {
            font-size: 72px;
            font-weight: 800;
            color: #4db8c4;
            line-height: 1;
            margin: 0 0 10px 0;
            letter-spacing: -2px;
        }

        html[data-theme="dark"] .error-code {
            color: #38bdf8;
        }

        .error-title {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 12px 0;
        }

        html[data-theme="dark"] .error-title {
            color: #f8fafc;
        }

        .error-desc {
            font-size: 15px;
            color: #64748b;
            line-height: 1.6;
            margin: 0 0 30px 0;
        }

        html[data-theme="dark"] .error-desc {
            color: #94a3b8;
        }

        .error-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .error-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .error-btn-primary {
            background-color: #4db8c4;
            color: white;
            box-shadow: 0 4px 12px rgba(77, 184, 196, 0.25);
        }

        .error-btn-primary:hover {
            background-color: #3aa6b2;
            transform: translateY(-2px);
        }

        .error-btn-secondary {
            background-color: transparent;
            color: #64748b;
            border: 1px solid #cbd5e1;
        }

        html[data-theme="dark"] .error-btn-secondary {
            color: #cbd5e1;
            border-color: #475569;
        }

        .error-btn-secondary:hover {
            background-color: rgba(148, 163, 184, 0.1);
        }
    </style>
</head>

<body>
    <div class="error-page-container">
        <div class="error-card">
            <a href="/">
                <img class="error-logo" src="/assets/images/logoUnired.png" alt="UNIRED Logo">
            </a>
            <div class="error-code">404</div>
            <h1 class="error-title">Página no encontrada</h1>
            <p class="error-desc">
                Lo sentimos, la página que estás buscando no existe, ha sido movida o no se encuentra disponible.
            </p>
            <div class="error-actions">
                <a href="/posts" class="error-btn error-btn-primary">Volver al Feed</a>
                <a href="/" class="error-btn error-btn-secondary">Página Principal</a>
            </div>
        </div>
    </div>
</body>

</html>
