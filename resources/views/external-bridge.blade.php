<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Harici bağlantı — OgraFi</title>
    @vite(['resources/css/app.css'])
</head>
<body>
    <main class="container" style="max-width:620px;margin:12vh auto;padding:24px">
        <section class="card card-body">
            <span class="repost-eyebrow">HARİCİ BAĞLANTI</span>
            <h1>OgraFi dışına çıkıyorsun</h1>
            <p><strong>{{ $targetHost }}</strong> adresine yönlendirileceksin.</p>
            <p class="meta">Bu bağlantı OgraFi tarafından yönetilmiyor. Devam etmeden önce adresi kontrol et.</p>
            <a class="btn btn-primary btn-rounded" href="{{ $targetUrl }}" rel="nofollow noopener noreferrer">Bağlantıya devam et</a>
            <a href="{{ route('home') }}" class="btn btn-secondary btn-rounded">OgraFi'ye dön</a>
        </section>
    </main>
</body>
</html>
