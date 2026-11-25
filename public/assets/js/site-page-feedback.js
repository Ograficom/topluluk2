(() => {
    const panel = document.querySelector('[data-site-feedback]');
    const trigger = document.querySelector('[data-site-feedback-toggle]');
    if (!panel || !trigger) return;
    const form = panel.querySelector('[data-site-feedback-form]');
    const status = panel.querySelector('.site-feedback-status');
    const buttons = [...panel.querySelectorAll('[data-vote]')];
    const submit = panel.querySelector('[type="submit"]');
    const retry = panel.querySelector('[data-site-feedback-retry]');
    let busy = false;
    let loaded = false;

    const say = (message, error = false) => {
        status.textContent = message;
        status.dataset.error = String(error);
        retry.hidden = !error || loaded;
    };
    const updateControls = () => {
        for (const button of [...buttons, submit]) button.disabled = busy || !loaded;
    };
    const render = (data) => {
        buttons.forEach(button => {
            const vote = button.dataset.vote;
            const active = data.vote === vote;
            button.setAttribute('aria-pressed', String(active));
            button.classList.toggle('btn-primary', active);
            button.classList.toggle('btn-secondary', !active);
            button.querySelector('[data-count]').textContent = new Intl.NumberFormat('tr-TR').format(data[vote]);
        });
        form.elements.vote.value = data.vote || '';
    };
    const request = async (payload = null) => {
        if (busy) return false;
        busy = true;
        updateControls();
        say(payload ? 'Kaydediliyor…' : 'Yükleniyor…');
        try {
            const response = await fetch(payload ? panel.dataset.postUrl : panel.dataset.url, {
                method: payload ? 'POST' : 'GET',
                credentials: 'same-origin',
                cache: 'no-store',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': form.elements._token.value,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                ...(payload ? {body: JSON.stringify(payload)} : {})
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                const validation = Object.values(data.errors || {}).flat()[0];
                throw new Error(validation || (response.status === 419
                    ? 'Oturumunuz yenilenmeli. Lütfen sayfayı yenileyin.'
                    : response.status === 429 ? 'Çok fazla işlem yaptınız. Biraz sonra tekrar deneyin.'
                    : response.status === 503 ? 'Geri bildirim sistemi henüz etkin değil. (503)'
                    : response.status === 404 ? 'Geri bildirim adresi bulunamadı. (404)'
                    : 'İşlem tamamlanamadı. (HTTP ' + response.status + ')'));
            }
            if (typeof data.like !== 'number' || typeof data.dislike !== 'number') throw new Error('Geri bildirim bilgileri alınamadı.');
            loaded = true;
            render(data);
            say(payload ? (payload.action === 'feedback' ? 'Teşekkürler, geri bildiriminiz kaydedildi.' : 'Değerlendirmeniz kaydedildi.') : '');
            return true;
        } catch (error) {
            say(error.message || 'Bağlantı hatası. Lütfen tekrar deneyin.', true);
            return false;
        } finally {
            busy = false;
            updateControls();
        }
    };
    panel.addEventListener('modal:open', () => {
        trigger.setAttribute('aria-expanded', 'true');
        request();
    });
    panel.addEventListener('modal:close', () => trigger.setAttribute('aria-expanded', 'false'));
    retry.addEventListener('click', () => request());
    buttons.forEach(button => button.addEventListener('click', () => request({ action:'vote', vote:button.dataset.vote })));
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (!form.reportValidity() || busy || !loaded) return;
        const ok = await request({
            action:'feedback',
            vote:form.elements.vote.value,
            email:form.elements.email.value,
            message:form.elements.message.value
        });
        if (ok) form.elements.message.value = '';
    });
})();
