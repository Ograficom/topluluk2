<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#f7f9fd">
    <title>{{ $code }} — OgraFi</title>
    <script>
        (() => {
            const root = document.documentElement;
            let theme = null;
            try { theme = localStorage.getItem('theme'); } catch (error) {}
            if (theme !== 'light' && theme !== 'dark') {
                theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            root.dataset.theme = theme;
            root.dataset.bsTheme = theme;
            root.style.colorScheme = theme;
        })();
    </script>
    <style>
        *,:before,:after{box-sizing:border-box}
        :root,[data-theme="light"]{
            --error-bg:#f7f9fd;--error-card:#fff;--error-text:#212529;--error-muted:#64748b;
            --error-border:#e7ebef;--error-blue:#0d6efd;--error-blue-soft:#edf5fe;
            --error-shadow:0 5px 20px rgba(15,23,42,.06)
        }
        [data-theme="dark"]{
            --error-bg:#0b0d12;--error-card:#15181f;--error-text:#f7f9fc;--error-muted:#8d98aa;
            --error-border:#232833;--error-blue:#2f80ff;--error-blue-soft:#172b4d;
            --error-shadow:0 8px 28px rgba(0,0,0,.28)
        }
        html,body{min-height:100%;margin:0}
        body{
            display:grid;place-items:center;min-height:100vh;padding:28px;
            color:var(--error-text);background:var(--error-bg);
            font-family:Roboto,Arial,sans-serif;font-size:15px;line-height:1.5;
            -webkit-font-smoothing:antialiased
        }
        .error-page{width:min(100%,560px);padding:clamp(28px,6vw,48px);text-align:center;background:var(--error-card);border:1px solid var(--error-border);border-radius:16px;box-shadow:var(--error-shadow)}
        .error-brand{display:inline-flex;align-items:center;justify-content:center;gap:10px;margin-bottom:34px;color:var(--error-text);font-family:Inter,Arial,sans-serif;font-size:21px;font-weight:800;letter-spacing:-.7px;text-decoration:none}
        .error-brand img{display:block;width:36px;height:36px;object-fit:contain}
        .error-visual{position:relative;display:grid;place-items:center;width:112px;height:112px;margin:0 auto 24px;border-radius:50%;background:var(--error-blue-soft);color:var(--error-blue)}
        .error-visual:before,.error-visual:after{position:absolute;content:"";border:1px solid var(--error-blue);border-radius:50%;opacity:.15}
        .error-visual:before{inset:10px}.error-visual:after{inset:-8px;border-style:dashed;opacity:.2}
        .error-visual svg{width:46px;height:46px;stroke:currentColor;stroke-width:1.8;fill:none;stroke-linecap:round;stroke-linejoin:round}
        .error-code{display:block;margin:0;color:var(--error-blue);font-family:Inter,Arial,sans-serif;font-size:clamp(62px,13vw,88px);font-weight:800;line-height:1;letter-spacing:-6px}
        .error-kicker{display:inline-flex;align-items:center;gap:7px;margin:20px 0 12px;padding:6px 11px;border-radius:6px;background:var(--error-blue-soft);color:var(--error-blue);font-family:Inter,Arial,sans-serif;font-size:11px;font-weight:700;letter-spacing:.055em;text-transform:uppercase}
        .error-kicker:before{width:6px;height:6px;border-radius:50%;background:currentColor;content:""}
        .error-title{margin:0 0 10px;color:var(--error-text);font-family:Inter,Arial,sans-serif;font-size:clamp(21px,4vw,26px);font-weight:750;letter-spacing:-.7px;line-height:1.3}
        .error-description{max-width:410px;margin:0 auto;color:var(--error-muted);font-size:14px;line-height:1.75}
        .error-actions{display:flex;flex-wrap:wrap;justify-content:center;gap:10px;margin-top:28px}
        .error-button{display:inline-flex;min-height:44px;align-items:center;justify-content:center;gap:8px;padding:0 19px;border:1px solid var(--error-border);border-radius:7px;background:var(--error-card);color:var(--error-text);font-family:Inter,Arial,sans-serif;font-size:13px;font-weight:650;text-decoration:none;transition:background .18s ease,border-color .18s ease,transform .18s ease}
        .error-button:hover{transform:translateY(-1px);border-color:var(--error-blue);color:var(--error-blue)}
        .error-button--primary{border-color:var(--error-blue);background:var(--error-blue);color:#fff}
        .error-button--primary:hover{border-color:var(--error-blue);background:var(--error-blue);color:#fff;filter:brightness(1.07)}
        .error-footer{margin-top:34px;padding-top:18px;border-top:1px solid var(--error-border);color:var(--error-muted);font-size:12px}
        .error-footer strong{color:var(--error-text);font-weight:700}
        @media(max-width:480px){body{padding:16px}.error-page{padding:28px 20px;border-radius:12px}.error-brand{margin-bottom:30px}.error-actions{flex-direction:column}.error-button{width:100%}}
        @media(prefers-reduced-motion:reduce){.error-button{transition:none}}
    </style>
</head>
<body>
    <main class="error-page">
        <a class="error-brand" href="{{ url('/') }}" aria-label="OgraFi ana sayfa">
            <img src="{{ asset('assets/img/meta/logo.svg') }}" alt="">
            <span>OgraFi</span>
        </a>
        <div class="error-visual" aria-hidden="true">
            <svg viewBox="0 0 48 48">
                @if ((string) $code === '404')
                    <circle cx="20.5" cy="20.5" r="11.5"></circle>
                    <path d="m29 29 9 9M16 20h9M20.5 15.5v9"></path>
                @elseif ((string) $code === '419')
                    <rect x="11" y="21" width="26" height="19" rx="4"></rect>
                    <path d="M17 21v-6a7 7 0 0 1 14 0v6M24 28v5"></path>
                @else
                    <path d="M24 7 43 40H5L24 7Z"></path>
                    <path d="M24 18v10M24 34h.01"></path>
                @endif
            </svg>
        </div>
        <span class="error-code">{{ $code }}</span>
        <span class="error-kicker">OgraFi · Sistem bildirimi</span>
        <h1 class="error-title">{{ $heading }}</h1>
        <p class="error-description">{{ $description }}</p>
        <nav class="error-actions" aria-label="Hata sayfası işlemleri">
            <a class="error-button error-button--primary" href="{{ $primaryUrl }}">{{ $primaryLabel }}</a>
            @if (!empty($secondaryUrl) && !empty($secondaryLabel))
                <a class="error-button" href="{{ $secondaryUrl }}">{{ $secondaryLabel }}</a>
            @endif
        </nav>
        <footer class="error-footer"><strong>OgraFi</strong> · Fikirlerin ve topluluğun buluşma noktası</footer>
    </main>
</body>
</html>
