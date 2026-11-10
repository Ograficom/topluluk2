<section class="cookie-consent" data-cookie-consent hidden aria-label="Çerez tercihleri">
    <div class="cookie-consent__notice" role="status">
        <p>Çerez kullanıyoruz. <a href="{{ route('pages.show', 'privacy') }}">Detaylar</a></p>
        <button type="button" class="cookie-consent__button cookie-consent__button--quiet" data-cookie-reject>Reddet</button>
        <button type="button" class="cookie-consent__button cookie-consent__button--primary" data-cookie-accept>Kabul et</button>
        <button type="button" class="cookie-consent__settings-link" data-cookie-details-open>Ayarlar</button>
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
        <p class="cookie-consent__legal"><a href="{{ route('pages.show', 'privacy') }}">Gizlilik politikası</a></p>
    </div>
</section>

<style>
.cookie-consent[hidden],.cookie-consent__details[hidden]{display:none!important}
.cookie-consent{position:fixed;left:16px;bottom:16px;z-index:10050;width:max-content;max-width:calc(100vw - 32px);color:#202124;font-family:inherit}
.cookie-consent__notice{display:flex;align-items:center;gap:10px;flex-wrap:wrap;background:#fff;border:1px solid #e7e7e7;border-radius:12px;padding:10px 12px;box-shadow:0 4px 18px rgba(0,0,0,.12)}
.cookie-consent__notice p{margin:0;font-size:12px;line-height:1.4;white-space:nowrap}
.cookie-consent__notice a,.cookie-consent__legal a{color:#2563eb;text-decoration:none}
.cookie-consent__notice a:hover,.cookie-consent__legal a:hover{text-decoration:underline}
.cookie-consent__button{min-height:30px;padding:6px 11px;border-radius:8px;font:600 11px/1.2 inherit;cursor:pointer;white-space:nowrap}
.cookie-consent__button--quiet{border:1px solid #e5e7eb;background:#f5f5f5;color:#333}
.cookie-consent__button--outline{border:1px solid #dbe2ea;background:#fff;color:#334155}
.cookie-consent__button--primary{border:1px solid #2563eb;background:#2563eb;color:#fff}
.cookie-consent__settings-link{border:0;background:transparent;padding:4px 2px;color:#555;font:12px inherit;cursor:pointer}
.cookie-consent__details{position:absolute;left:0;bottom:calc(100% + 10px);width:min(420px,calc(100vw - 32px));max-height:min(72vh,620px);overflow:auto;background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:18px;box-shadow:0 8px 30px rgba(0,0,0,.16)}
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
@media(max-width:520px){.cookie-consent{left:10px;bottom:10px;max-width:calc(100vw - 20px)}.cookie-consent__notice{gap:7px;padding:9px}.cookie-consent__notice p{font-size:11px}.cookie-consent__button{padding:6px 8px;font-size:10px}.cookie-consent__details{width:calc(100vw - 20px)}.cookie-consent__option small{max-width:190px}}
</style>

<script>
(() => {
    const root = document.querySelector('[data-cookie-consent]');
    if (!root || root.dataset.initialized) return;
    root.dataset.initialized = 'true';

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
    const openSettings = () => {
        const consent = getConsent();
        categories.forEach((key) => {
            const input = root.querySelector('[data-cookie-category="' + key + '"]');
            if (input) input.checked = Boolean(consent?.categories?.[key]);
        });
        root.hidden = false;
        details.hidden = false;
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
