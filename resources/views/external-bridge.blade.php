<!doctype html>
<html lang="tr">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>OgraFi bağlantı yönlendirmesi</title></head>
<body>
    <main>
        <p>Bağlantı hazırlanıyor…</p>
        <a href="{{ request('url', route('home')) }}" rel="noopener noreferrer">Devam et</a>
    </main>
    <script>
        (() => {
            const target = @js(request('url'));
            if (!target) return;
            try {
                const parsed = new URL(target, window.location.origin);
                if (parsed.protocol === 'http:' || parsed.protocol === 'https:') window.location.replace(parsed.href);
            } catch (_) {}
        })();
    </script>
</body>
</html>
