<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#101827">
    <title>{{ $code }} — OgraFi</title>
    <style>
        :root{color-scheme:dark;--bg:#0b1220;--panel:#111c2e;--line:rgba(148,163,184,.16);--muted:#94a3b8;--text:#f8fafc;--accent:#38bdf8}
        *{box-sizing:border-box}
        html,body{min-height:100%;margin:0}
        body{display:grid;place-items:center;padding:28px;background:radial-gradient(ellipse at 50% 0%,rgba(14,165,233,.13),transparent 48%),var(--bg);color:var(--text);font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
        .error{width:min(100%,620px);padding:clamp(28px,6vw,56px);text-align:center;background:linear-gradient(145deg,rgba(255,255,255,.035),transparent 55%),var(--panel);border:1px solid var(--line);border-radius:24px;box-shadow:0 28px 90px rgba(0,0,0,.32)}
        .brand{display:inline-flex;align-items:center;gap:10px;margin-bottom:42px;color:var(--text);font-size:21px;font-weight:800;letter-spacing:-.8px;text-decoration:none}
        .brand-mark{display:grid;place-items:center;width:34px;height:34px;border-radius:11px;background:linear-gradient(135deg,#38bdf8,#2563eb);color:#071321;font-size:18px;font-weight:900}
        .code{display:block;margin:0;color:var(--accent);font-size:clamp(76px,17vw,116px);font-weight:850;line-height:.95;letter-spacing:-.09em}
        h1{margin:24px 0 10px;font-size:clamp(23px,4vw,30px);letter-spacing:-.7px}
        .description{max-width:430px;margin:0 auto;color:var(--muted);font-size:15px;line-height:1.75}
        .actions{display:flex;flex-wrap:wrap;justify-content:center;gap:12px;margin-top:30px}
        .button{display:inline-flex;min-height:46px;align-items:center;justify-content:center;padding:0 20px;border:1px solid var(--line);border-radius:12px;color:var(--text);font-size:14px;font-weight:700;text-decoration:none;transition:transform .18s ease,border-color .18s ease,background .18s ease}
        .button:hover{transform:translateY(-2px);border-color:rgba(56,189,248,.55)}
        .button-primary{border-color:transparent;background:var(--accent);color:#082033}
        .button-primary:hover{background:#7dd3fc}
        .foot{margin-top:42px;padding-top:20px;border-top:1px solid var(--line);color:#64748b;font-size:12px}
        @media(max-width:480px){body{padding:16px}.error{border-radius:18px}.brand{margin-bottom:34px}.actions{flex-direction:column}.button{width:100%}}
        @media(prefers-reduced-motion:reduce){*,*::before,*::after{scroll-behavior:auto!important;transition:none!important}}
    </style>
</head>
<body>
    <main class="error" role="main">
        <a class="brand" href="{{ url('/') }}" aria-label="OgraFi ana sayfa">
            <span class="brand-mark" aria-hidden="true">O</span>
            <span>OgraFi</span>
        </a>
        <span class="code" aria-hidden="true">{{ $code }}</span>
        <h1>{{ $heading }}</h1>
        <p class="description">{{ $description }}</p>
        <nav class="actions" aria-label="Hata sayfası işlemleri">
            <a class="button button-primary" href="{{ $primaryUrl }}">{{ $primaryLabel }}</a>
            @if (!empty($secondaryUrl) && !empty($secondaryLabel))
                <a class="button" href="{{ $secondaryUrl }}">{{ $secondaryLabel }}</a>
            @endif
        </nav>
        <div class="foot">OgraFi · Fikirlerin ve topluluğun buluşma noktası</div>
    </main>
</body>
</html>
