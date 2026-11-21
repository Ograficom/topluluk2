<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="{{ $settings?->pwa_theme_color ?? '#2563eb' }}">
    <title>{{ $settings?->pwa_offline_title ?? 'İnternet bağlantısı yok' }} — {{ $settings?->pwa_name ?? 'OgraFi' }}</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:{{ $settings?->pwa_background_color ?? '#f7f9fd' }};color:#172033;font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}.offline{width:min(100%,440px);padding:36px 28px;text-align:center;background:#fff;border:1px solid #e5e7eb;border-radius:22px;box-shadow:0 18px 55px rgba(15,23,42,.08)}.mark{width:64px;height:64px;margin:0 auto 22px;border-radius:18px;background:{{ $settings?->pwa_theme_color ?? '#2563eb' }};display:grid;place-items:center;color:#fff;font-size:30px;font-weight:800}.offline h1{margin:0 0 10px;font-size:23px}.offline p{margin:0;color:#64748b;line-height:1.7}.offline button{margin-top:24px;border:0;border-radius:12px;padding:12px 20px;background:{{ $settings?->pwa_theme_color ?? '#2563eb' }};color:#fff;font-size:15px;font-weight:650;cursor:pointer}
    </style>
</head>
<body>
    <main class="offline">
        <div class="mark">O</div>
        <h1>{{ $settings?->pwa_offline_title ?? 'İnternet bağlantısı yok' }}</h1>
        <p>{{ $settings?->pwa_offline_message ?? 'Bağlantın yeniden geldiğinde sayfayı yenileyebilirsin.' }}</p>
        <button type="button" onclick="window.location.reload()">Yeniden dene</button>
    </main>
</body>
</html>
