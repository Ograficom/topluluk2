<section class="cookie-consent" data-cookie-consent hidden aria-label="Çerez tercihleri">
    <div class="cookie-consent__notice" role="status">
        <p>Çerez kullanıyoruz. <a href="{{ url('/page/privacy-policy') }}">Detaylar</a></p>
        <button type="button" class="cookie-consent__button cookie-consent__button--primary" data-cookie-accept>TAMAM</button>
    </div>

    <div class="cookie-consent__details" data-cookie-details hidden>
        <div class="cookie-consent__details-head">
            <div>
                <strong>Çerez tercihleri</strong>
                <p>OgraFi'nin düzgün çalışması için zorunlu çerezler kullanılır. Diğer çerezleri tercihine göre açıp kapatabilirsin.</p>
            </div>
            <button type="button" class="cookie-consent__close" data-cookie-details-close aria-label="Kapat">×</button>
        </div>

        <div class="cookie-consent__options">
            <div class="cookie-consent__option">
                <div><strong>Zorunlu çerezler</strong><small>Oturum, güvenlik ve temel site işlevleri için gereklidir. Kapatılamaz.</small></div>
                <label class="cookie-consent__toggle"><input type="checkbox" checked disabled><span></span><em>Her zaman açık</em></label>
            </div>
            <div class="cookie-consent__option">
                <div><strong>Tercih çerezleri</strong><small>Tema ve benzeri seçimlerini hatırlamamıza yardımcı olur.</small></div>
                <label class="cookie-consent__toggle"><input type="checkbox" data-cookie-category="preferences"><span></span><em class="sr-only">Tercih çerezlerine izin ver</em></label>
            </div>
            <div class="cookie-consent__option">
                <div><strong>Analitik çerezler</strong><small>Site kullanımını ölçerek OgraFi'yi geliştirmemize yardımcı olur.</small></div>
                <label class="cookie-consent__toggle"><input type="checkbox" data-cookie-category="analytics"><span></span><em class="sr-only">Analitik çerezlere izin ver</em></label>
            </div>
            <div class="cookie-consent__option">
                <div><strong>Pazarlama çerezleri</strong><small>Reklam performansını ölçmek ve ilgili içerikler sunmak için kullanılır.</small></div>
                <label class="cookie-consent__toggle"><input type="checkbox" data-cookie-category="marketing"><span></span><em class="sr-only">Pazarlama çerezlerine izin ver</em></label>
            </div>
        </div>

        <div class="cookie-consent__actions">
            <button type="button" class="cookie-consent__button cookie-consent__button--quiet" data-cookie-reject>İsteğe bağlıları reddet</button>
            <button type="button" class="cookie-consent__button cookie-consent__button--outline" data-cookie-save>Seçimleri kaydet</button>
            <button type="button" class="cookie-consent__button cookie-consent__button--primary" data-cookie-accept>Hepsini kabul et</button>
        </div>
        <p class="cookie-consent__legal"><a href="{{ url('/page/privacy-policy') }}">Gizlilik politikası</a></p>
    </div>
</section>

<style>
.cookie-consent[hidden],.cookie-consent__details[hidden]{display:none!important}
.cookie-consent{position:fixed;right:auto;left:16px;bottom:16px;z-index:10050;width:max-content;max-width:calc(100vw - 32px);color:#202124;font-family:inherit}
.cookie-consent__notice{display:flex;align-items:center;gap:10px;flex-wrap:wrap;background:#fff;border:1px solid #e7e7e7;border-radius:12px;padding:10px 12px;box-shadow:0 4px 18px rgba(0,0,0,.12)}
.cookie-consent__notice p{margin:0;font-size:12px;line-height:1.4;white-space:nowrap}
.cookie-consent__notice a,.cookie-consent__legal a{color:#2563eb;text-decoration:none}
.cookie-consent__notice a:hover,.cookie-consent__legal a:hover{text-decoration:underline}
.cookie-consent__button{min-height:30px;padding:6px 11px;border-radius:8px;font-family:inherit;font-size:11px;font-weight:600;line-height:1.2;cursor:pointer;white-space:nowrap}
.cookie-consent__button--quiet{border:1px solid #e5e7eb;background:#f5f5f5;color:#333}
.cookie-consent__button--outline{border:1px solid #dbe2ea;background:#fff;color:#334155}
.cookie-consent__button--primary{border:1px solid #2563eb;background:#2563eb;color:#fff}
.cookie-consent__settings-link{border:0;background:transparent;padding:4px 2px;color:#555;font-family:inherit;font-size:12px;cursor:pointer}
.cookie-consent__details{position:fixed;left:50%;top:50%;right:auto;bottom:auto;transform:translate(-50%,-50%);box-sizing:border-box;width:min(420px,calc(100vw - 32px));max-height:min(72vh,620px);overflow:auto;background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:18px;box-shadow:0 8px 30px rgba(0,0,0,.16)}
.cookie-consent__details-head{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;margin-bottom:12px}
.cookie-consent__details-head strong{font-size:16px}
.cookie-consent__details-head p,.cookie-consent__option small,.cookie-consent__legal{font-size:12px;line-height:1.5;color:#64748b}
.cookie-consent__details-head p{margin:5px 0 0}
.cookie-consent__close{border:0;background:#f3f4f6;border-radius:50%;width:28px;height:28px;font-size:20px;line-height:1;cursor:pointer;color:#555;flex:none}
.cookie-consent__options{border-top:1px solid #edf0f4}
.cookie-consent__option{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:11px 0;border-bottom:1px solid #edf0f4}
.cookie-consent__option strong{display:block;font-size:13px}
.cookie-consent__option small{display:block;margin-top:3px;max-width:275px}
.cookie-consent__toggle{display:flex;align-items:center;gap:7px;flex-shrink:0;cursor:pointer}
.cookie-consent__toggle input{position:absolute;opacity:0;width:1px;height:1px}
.cookie-consent__toggle span{width:36px;height:21px;border-radius:99px;background:#cbd5e1;position:relative}
.cookie-consent__toggle span:after{content:"";position:absolute;width:15px;height:15px;left:3px;top:3px;border-radius:50%;background:#fff;transition:transform .15s}
.cookie-consent__toggle input:checked+span{background:#2563eb}
.cookie-consent__toggle input:checked+span:after{transform:translateX(15px)}
.cookie-consent__toggle input:disabled+span{background:#94a3b8;cursor:not-allowed}
.cookie-consent__toggle em{font-size:10px;font-style:normal;color:#64748b;white-space:nowrap}
.cookie-consent__actions{display:flex;gap:7px;flex-wrap:wrap;margin-top:14px}
.cookie-consent__legal{margin:10px 0 0}
.cookie-consent__toggle .sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
@media(max-width:920px){.cookie-consent{left:10px!important;right:10px!important;bottom:calc(64px + env(safe-area-inset-bottom))!important;width:auto!important;max-width:none!important}.cookie-consent__notice{width:100%;box-sizing:border-box;display:flex;flex-wrap:nowrap;align-items:center;justify-content:space-between;gap:10px;padding:8px 12px;border-radius:14px;box-shadow:0 4px 18px rgba(0,0,0,.12)}.cookie-consent__notice p{min-width:0;font-size:12px;white-space:nowrap}.cookie-consent__button{flex:0 0 auto;min-height:32px;padding:7px 13px;font-size:11px;border-radius:18px}.cookie-consent__details{width:calc(100vw - 20px)}.cookie-consent__option small{max-width:190px}}
</style>

<script>
(() => {
    const root = document.querySelector('[data-cookie-consent]');
    if (!root || root.dataset.initialized) return;
    root.dataset.initialized = 'true';

    const alignCookiePopup = () => {
        const feed = document.querySelector('main.container .main:not(.main__grid-full) > .feed');
        if (!feed || window.innerWidth <= 920) {
            root.style.left = '10px';
            root.style.right = '10px';
            root.style.width = 'auto';
            root.style.maxWidth = 'none';
            root.style.bottom = 'calc(64px + env(safe-area-inset-bottom))';
            return;
        }

        const feedRect = feed.getBoundingClientRect();
        const popupWidth = root.getBoundingClientRect().width;
        const gap = 8;
        const left = Math.max(10, feedRect.left - popupWidth - gap);
        root.style.left = Math.round(left) + 'px';
        root.style.right = 'auto';
    };

    alignCookiePopup();
    window.addEventListener('resize', alignCookiePopup, { passive: true });
    window.addEventListener('scroll', alignCookiePopup, { passive: true });

    const storageKey = 'ografi_cookie_consent_v1';
    const categories = ['preferences', 'analytics', 'marketing'];
    const details = root.querySelector('[data-cookie-details]');
    const readConsent = () => {
        try {
            const raw = localStorage.getItem(storageKey);
            return raw ? JSON.parse(raw) : null;
        } catch { return null; }
    };
    const getConsent = () => {
        const value = readConsent();
        return value && value.version === 1 && value.categories ? value : null;
    };
    const saveConsent = (selected) => {
        const consent = {
            version: 1,
            necessary: true,
            categories: Object.fromEntries(categories.map((key) => [key, Boolean(selected[key])])),
            updatedAt: new Date().toISOString()
        };
        try {
            localStorage.setItem(storageKey, JSON.stringify(consent));
        } catch {}
        document.cookie = 'ografi_cookie_consent=' + encodeURIComponent(JSON.stringify(consent)) + '; Path=/; Max-Age=15552000; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
        root.hidden = true;
        document.documentElement.dataset.cookieConsent = 'saved';
        window.dispatchEvent(new CustomEvent('ografi:cookie-consent-updated', { detail: consent }));
        activateApprovedScripts(consent);
    };
    const activateApprovedScripts = (consent) => {
        document.querySelectorAll('script[type="text/plain"][data-consent-category]').forEach((placeholder) => {
            const category = placeholder.dataset.consentCategory;
            if (!consent.categories?.[category] || placeholder.dataset.loaded === 'true') return;
            const script = document.createElement('script');
            for (const attribute of placeholder.attributes) {
                if (!['type', 'data-consent-category', 'data-loaded'].includes(attribute.name)) {
                    script.setAttribute(attribute.name, attribute.value);
                }
            }
            script.textContent = placeholder.textContent;
            placeholder.dataset.loaded = 'true';
            placeholder.after(script);
        });
    };
    const alignDetailsPopup = () => {
        if (details.hidden) return;
        const popupRect = details.getBoundingClientRect();
        const feed = window.innerWidth > 920
            ? document.querySelector('main.container .main:not(.main__grid-full) > .feed')
            : null;
        const anchor = feed ? feed.getBoundingClientRect() : { left: 0, right: window.innerWidth, top: 0, bottom: window.innerHeight, width: window.innerWidth, height: window.innerHeight };
        const left = Math.min(
            Math.max(anchor.left + (anchor.width - popupRect.width) / 2, 16),
            Math.max(16, window.innerWidth - popupRect.width - 16)
        );
        const top = Math.max(16, (window.innerHeight - popupRect.height) / 2);
        details.style.left = Math.round(left) + 'px';
        details.style.top = Math.round(top) + 'px';
        details.style.right = 'auto';
        details.style.bottom = 'auto';
        details.style.transform = 'none';
    };

    window.addEventListener('resize', alignDetailsPopup, { passive: true });
    window.addEventListener('scroll', alignDetailsPopup, { passive: true });

    const openSettings = () => {
        const consent = getConsent();
        categories.forEach((key) => {
            const input = root.querySelector('[data-cookie-category="' + key + '"]');
            if (input) input.checked = Boolean(consent?.categories?.[key]);
        });
        root.hidden = false;
        details.hidden = false;
        requestAnimationFrame(alignDetailsPopup);
    };
    root.querySelectorAll('[data-cookie-accept]').forEach((button) => button.addEventListener('click', () => {
        saveConsent({ preferences: true, analytics: true, marketing: true });
    }));
    root.querySelectorAll('[data-cookie-reject]').forEach((button) => button.addEventListener('click', () => {
        saveConsent({ preferences: false, analytics: false, marketing: false });
    }));
    root.querySelector('[data-cookie-save]')?.addEventListener('click', () => {
        const selected = {};
        categories.forEach((key) => {
            selected[key] = Boolean(root.querySelector('[data-cookie-category="' + key + '"]')?.checked);
        });
        saveConsent(selected);
    });
    root.querySelector('[data-cookie-details-open]')?.addEventListener('click', openSettings);
    root.querySelector('[data-cookie-details-close]')?.addEventListener('click', () => { details.hidden = true; });
    document.querySelectorAll('[data-cookie-settings-open]').forEach((button) => button.addEventListener('click', openSettings));

    const existing = getConsent();
    if (existing) {
        root.hidden = true;
        activateApprovedScripts(existing);
    } else {
        root.hidden = false;
        details.hidden = true;
    }
})();
</script>
