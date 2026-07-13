{{-- resources/views/errors/404.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Sayfa Bulunamadı | Ografi</title>
    <meta name="robots" content="noindex, nofollow">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg: #f5f7fb;
            --card: #ffffff;
            --text: #111827;
            --muted: #6b7280;
            --border: #e5e7eb;
            --soft: #f3f4f6;
            --blue: #2563eb;
            --blue-hover: #1d4ed8;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #0f172a;
                --card: #111827;
                --text: #f9fafb;
                --muted: #9ca3af;
                --border: rgba(255, 255, 255, .10);
                --soft: rgba(255, 255, 255, .06);
                --blue: #3b82f6;
                --blue-hover: #2563eb;
            }
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Roboto", Arial, Helvetica, sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px;
        }

        .page {
            width: 100%;
            max-width: 430px;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 24px;
            text-align: center;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
            animation: openPopup .22s ease both;
        }

        .logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            text-decoration: none;
        }

        .logo img {
            display: block;
            width: auto;
            height: 42px;
            max-width: 160px;
            object-fit: contain;
        }

        h1 {
            margin: 0;
            font-size: 25px;
            line-height: 1.25;
            letter-spacing: -.02em;
            font-weight: 500;
        }

        p {
            margin: 12px auto 0;
            max-width: 335px;
            color: var(--muted);
            font-size: 14.5px;
            line-height: 1.65;
            font-weight: 400;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 22px;
        }

        .btn {
            width: 100%;
            height: 43px;
            border-radius: 12px;
            border: 1px solid var(--border);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-family: inherit;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background .18s ease, border-color .18s ease, color .18s ease;
        }

        .btn-primary {
            background: var(--blue);
            border-color: var(--blue);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--blue-hover);
            border-color: var(--blue-hover);
        }

        .btn-light {
            background: transparent;
            color: var(--text);
        }

        .btn-light:hover {
            background: var(--soft);
        }

        .bottom-text {
            margin-top: 18px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
            color: var(--muted);
            font-size: 12.5px;
            line-height: 1.5;
            font-weight: 400;
        }

        @keyframes openPopup {
            from {
                opacity: 0;
                transform: scale(.97) translateY(8px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 14px;
            }

            .card {
                padding: 22px 18px;
                border-radius: 16px;
            }

            .logo img {
                height: 38px;
                max-width: 145px;
            }

            .actions {
                flex-direction: column;
            }

            h1 {
                font-size: 23px;
            }
        }
    </style>
</head>
<body>
    <main class="page" aria-labelledby="error-title">
        <section class="card">
            <a href="{{ url('/') }}" class="logo" aria-label="Ografi ana sayfa">
                <img src="{{ asset('images/ografi-logo.png') }}?v=20260714a" alt="Ografi">
            </a>

            <h1 id="error-title">Sayfa bulunamadı</h1>

            <p>
                Açmaya çalıştığın sayfa silinmiş, taşınmış veya bağlantı hatalı olabilir.
                Ana sayfaya dönebilir ya da önceki sayfaya geri gidebilirsin.
            </p>

            <div class="actions">
                <a href="{{ url('/') }}" class="btn btn-primary">Ana sayfa</a>
                <button type="button" class="btn btn-light" onclick="goBackOrHome()">Geri dön</button>
            </div>

            <div class="bottom-text">
                Bağlantının doğru olduğundan emin ol. Sorun devam ederse daha sonra tekrar deneyebilirsin.
            </div>
        </section>
    </main>

    <script>
        function goBackOrHome() {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = "{{ url('/') }}";
            }
        }
    </script>
</body>
</html>
