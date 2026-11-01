@if($showBanner && $policy)
    <aside class="ografi-cookie-banner" data-cookie-banner role="dialog" aria-label="Çerez tercihleri">
        <div class="ografi-cookie-banner__content">
            <strong>{{ $policy->banner_title ?: 'Çerez tercihleri' }}</strong>
            <p>{{ $policy->banner_message ?: 'Sitemizin düzgün çalışması ve deneyiminizi iyileştirmek için gerekli çerezleri kullanıyoruz.' }}</p>
            <a href="{{ url('/cerez-politikasi') }}">Çerez politikası</a>
        </div>
        <div class="ografi-cookie-banner__actions">
            <button type="button" class="btn btn-secondary btn-sm" data-cookie-decision="reject">Reddet</button>
            <button type="button" class="btn btn-primary btn-sm" data-cookie-decision="accept">Kabul et</button>
        </div>
    </aside>
    <script>
    (() => {
        const banner = document.querySelector('[data-cookie-banner]');
        if (!banner || banner.dataset.initialized) return;
        banner.dataset.initialized = '1';
        banner.addEventListener('click', async (event) => {
            const button = event.target.closest('[data-cookie-decision]');
            if (!button) return;
            button.disabled = true;
            try {
                const response = await fetch(@js(route('cookie-consent.store')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({ decision: button.dataset.cookieDecision })
                });
                if (!response.ok) throw new Error('Çerez tercihi kaydedilemedi.');
                banner.remove();
            } catch (error) {
                button.disabled = false;
                console.error(error);
            }
        });
    })();
    </script>
@endif
