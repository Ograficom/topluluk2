@php($siteKey = config('services.recaptcha.site_key'))
@if($siteKey)
    <div
        x-data="{
            token: '',
            refresh() {
                this.token = '';
                const input = document.querySelector('input[name=&quot;data[recaptcha_token]&quot;], input[name=&quot;recaptcha_token&quot;]');
                if (input) input.value = '';
            },
            async verify() {
                if (!window.grecaptcha || !window.grecaptcha.execute) return;
                try {
                    await window.grecaptcha.ready();
                    this.token = await window.grecaptcha.execute(@js($siteKey), { action: 'admin_login' });
                    const input = document.querySelector('input[name=&quot;data[recaptcha_token]&quot;], input[name=&quot;recaptcha_token&quot;]');
                    if (input) {
                        input.value = this.token;
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                } catch (error) {
                    console.error('reCAPTCHA doğrulaması başlatılamadı.', error);
                }
            }
        }"
        x-init="verify(); window.addEventListener('admin-recaptcha-refresh', () => verify())"
        class="text-xs text-gray-500"
    >
        <span>Güvenlik doğrulaması etkin.</span>
    </div>
    <script src="https://www.google.com/recaptcha/api.js?render={{ urlencode($siteKey) }}" async defer></script>
@endif
