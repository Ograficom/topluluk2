<section class="cookie-consent" data-cookie-consent hidden aria-label="Çerez tercihleri">
    <div class="cookie-consent__backdrop" data-cookie-close></div>
    <div class="cookie-consent__panel" role="dialog" aria-modal="true" aria-labelledby="cookieConsentTitle">
        <div class="cookie-consent__intro">
            <span class="cookie-consent__eyebrow">GİZLİLİK TERCİHLERİ</span>
            <h2 id="cookieConsentTitle">Çerez tercihlerini yönet</h2>
            <p>OgraFi'nin düzgün çalışması için zorunlu çerezler kullanılır. Diğer çerezleri kabul edip etmemek sana bağlı. Tercihini istediğin zaman değiştirebilirsin.</p>
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
            <button type="button" class="cookie-consent__button cookie-consent__button--outline" data-cookie-save>Seçimlerimi kaydet</button>
            <button type="button" class="cookie-consent__button cookie-consent__button--primary" data-cookie-accept>Kabul et</button>
        </div>
        <p class="cookie-consent__legal">Tercihlerini daha sonra sayfanın altındaki “Çerez ayarları” bağlantısından değiştirebilirsin. <a href="{{ route('pages.show', 'privacy') }}">Gizlilik politikası</a></p>
    </div>
</section>

<style>
.cookie-consent[hidden]{display:none!important}
.cookie-consent{position:fixed;inset:0;z-index:10050;display:grid;align-items:end;justify-items:center;padding:20px}
.cookie-consent__backdrop{position:absolute;inset:0;background:rgba(15,23,42,.48);backdrop-filter:blur(2px)}
.cookie-consent__panel{position:relative;width:min(100%,620px);max-height:min(88vh,760px);overflow:auto;background:#fff;color:#172033;border:1px solid #e5eaf1;border-radius:16px;padding:24px;box-shadow:0 18px 60px rgba(15,23,42,.2)}
.footer__legal{display:flex;align-items:center;gap:12px;flex-wrap:wrap}.footer__legal button{border:0;background:transparent;padding:0;font:inherit;color:inherit;cursor:pointer;text-decoration:underline;text-underline-offset:3px}\n.cookie-consent__eyebrow{font-size:11px;letter-spacing:.1em;font-weight:700;color:#64748b}
.cookie-consent__intro h2{font-size:22px;line-height:1.25;margin:7px 0 8px;font-weight:700}
.cookie-consent__intro p,.cookie-consent__option small,.cookie-consent__legal{font-size:13px;line-height:1.55;color:#64748b}
.cookie-consent__intro p{margin:0 0 18px}
.cookie-consent__options{border-top:1px solid #edf0f4}
.cookie-consent__option{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:13px 0;border-bottom:1px solid #edf0f4}
.cookie-consent__option strong{display:block;font-size:14px;font-weight:650}
.cookie-consent__option small{display:block;margin-top:3px;max-width:410px}
.cookie-consent__toggle{display:flex;align-items:center;gap:8px;flex-shrink:0;cursor:pointer}
.cookie-consent__toggle input{position:absolute;opacity:0;width:1px;height:1px}
.cookie-consent__toggle span{width:38px;height:22px;border-radius:99px;background:#cbd5e1;position:relative;transition:background .15s}
.cookie-consent__toggle span:after{content:"";position:absolute;width:16px;height:16px;left:3px;top:3px;border-radius:50%;background:#fff;transition:transform .15s}
.cookie-consent__toggle input:checked+span{background:#2563eb}
.cookie-consent__toggle input:checked+span:after{transform:translateX(16px)}
.cookie-consent__toggle input:disabled+span{background:#94a3b8;cursor:not-allowed}
.cookie-consent__toggle em{font-size:10px;font-style:normal;color:#64748b;white-space:nowrap}
.cookie-consent__actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:18px}
.cookie-consent__button{min-height:40px;padding:9px 13px;border-radius:9px;font-size:12px;font-weight:650;cursor:pointer}
.cookie-consent__button--quiet{border:1px solid #dbe2ea;background:#fff;color:#334155}
.cookie-consent__button--outline{border:1px solid #2563eb;background:#fff;color:#1d4ed8}
.cookie-consent__button--primary{border:1px solid #2563eb;background:#2563eb;color:#fff;margin-left:auto}
.cookie-consent__legal{margin:12px 0 0;font-size:11px}
.cookie-consent__legal a{color:#1d4ed8;text-decoration:underline}
.cookie-consent__toggle .sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
@media(max-width:600px){.cookie-consent{padding:0;align-items:end}.cookie-consent__panel{width:100%;max-height:90vh;border-radius:16px 16px 0 0;padding:20px 16px calc(18px + env(safe-area-inset-bottom))}.cookie-consent__actions{display:grid;grid-template-columns:1fr 1fr}.cookie-consent__button--primary{grid-column:1/-1;margin:0;grid-row:1}.cookie-consent__button{width:100%}.cookie-consent__option small{max-width:230px}}
</style>

<script>
(() => {
    const root = document.querySelector('[data-cookie-consent]');
    if (!root || root.dataset.initialized) return;
    root.dataset.initialized = 'true';

    const storageKey = 'ografi_cookie_consent_v1';
    const categories = ['preferences', 'analytics', 'marketing'];
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
        localStorage.setItem(storageKey, JSON.stringify(consent));
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
    const open = () => {
        const consent = getConsent();
        categories.forEach((key) => {
            const input = root.querySelector('[data-cookie-category="' + key + '"]');
            if (input) input.checked = Boolean(consent?.categories?.[key]);
        });
        root.hidden = false;
        root.querySelector('[data-cookie-accept]')?.focus();
    };
    root.querySelector('[data-cookie-accept]')?.addEventListener('click', () => {
        saveConsent({ preferences: true, analytics: true, marketing: true });
    });
    root.querySelector('[data-cookie-reject]')?.addEventListener('click', () => {
        saveConsent({ preferences: false, analytics: false, marketing: false });
    });
    root.querySelector('[data-cookie-save]')?.addEventListener('click', () => {
        const selected = {};
        categories.forEach((key) => {
            selected[key] = Boolean(root.querySelector('[data-cookie-category="' + key + '"]')?.checked);
        });
        saveConsent(selected);
    });
    root.querySelectorAll('[data-cookie-close]').forEach((el) => el.addEventListener('click', () => { root.hidden = true; }));
    document.querySelectorAll('[data-cookie-settings-open]').forEach((button) => button.addEventListener('click', open));

    const existing = getConsent();
    if (existing) {
        root.hidden = true;
        activateApprovedScripts(existing);
    } else {
        open();
    }
})();
</script>
