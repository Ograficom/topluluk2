@props([
    'siteKey',
])

@once
    <script data-recaptcha-v3 src="https://www.google.com/recaptcha/api.js?render={{ $siteKey }}" async defer></script>
    <script>
        (function () {
            const siteKey = @json($siteKey);

            function findTokenInput(form) {
                return form.querySelector('input[name="recaptcha_token"]');
            }

            async function ensureToken(form) {
                if (!window.grecaptcha) {
                    throw new Error('grecaptcha_not_loaded');
                }

                const action = form.getAttribute('data-recaptcha-action') || 'submit';

                return new Promise((resolve, reject) => {
                    window.grecaptcha.ready(() => {
                        window.grecaptcha.execute(siteKey, { action })
                            .then(resolve)
                            .catch(reject);
                    });
                });
            }

            document.addEventListener('submit', async (event) => {
                const form = event.target;
                if (!(form instanceof HTMLFormElement)) return;
                if (!form.hasAttribute('data-recaptcha-v3')) return;
                if ((form.getAttribute('data-recaptcha-site-key') || '') !== siteKey) return;

                const input = findTokenInput(form);
                if (!input) return;

                if (form.dataset.recaptchaSubmitting === '1') return;
                form.dataset.recaptchaSubmitting = '1';

                event.preventDefault();

                try {
                    const token = await ensureToken(form);
                    input.value = token;
                    form.submit();
                } catch (e) {
                    form.dataset.recaptchaSubmitting = '0';
                    form.submit();
                }
            }, true);
        })();
    </script>
@endonce

