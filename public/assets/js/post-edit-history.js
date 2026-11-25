(() => {
    const requests = new WeakMap();
    const formatDate = new Intl.DateTimeFormat('tr-TR', {dateStyle:'medium',timeStyle:'short'});
    async function load(root, page = 1) {
        const status = root.querySelector('[data-edit-history-status]');
        const entries = root.querySelector('[data-edit-history-entries]');
        const more = root.querySelector('[data-edit-history-more]');
        const row = root.querySelector('[data-edit-history-more-row]');
        if (!status || !entries || !more || !row) return;
        requests.get(root)?.abort();
        const controller = new AbortController();
        requests.set(root, controller);
        if (page === 1) entries.replaceChildren();
        status.textContent = 'Yükleniyor…';
        row.hidden = true;
        more.disabled = true;
        try {
            const url = new URL(root.dataset.editHistoryUrl, location.origin);
            url.searchParams.set('page', page);
            const response = await fetch(url, {credentials:'same-origin',cache:'no-store',signal:controller.signal,headers:{Accept:'application/json'}});
            if (!response.ok) throw new Error('HTTP ' + response.status);
            const data = await response.json();
            if (controller.signal.aborted) return;
            status.textContent = data.count ? data.count + ' kez güncellendi' : 'Kayıtlı güncelleme bulunmuyor.';
            for (const entry of data.entries) {
                const li = document.createElement('li');
                li.className = 'post-edit-history-entry';
                const name = document.createElement('strong');
                name.textContent = entry.editor;
                const date = document.createElement('time');
                date.dateTime = entry.date;
                const parsed = new Date(entry.date);
                date.textContent = Number.isNaN(parsed.getTime()) ? '' : formatDate.format(parsed);
                li.append(name,date);
                entries.append(li);
            }
            more.dataset.page = data.next_page || '';
            more.textContent = 'Daha fazla göster';
            row.hidden = !data.next_page;
        } catch (error) {
            if (!controller.signal.aborted) {
                status.textContent = 'Geçmiş yüklenemedi.';
                more.dataset.page = String(page);
                more.textContent = 'Tekrar dene';
                row.hidden = false;
            }
        } finally {
            if (requests.get(root) === controller) more.disabled = false;
        }
    }
    document.addEventListener('dropdown:open', event => {
        const root = event.target;
        if (!(root instanceof HTMLElement) || !root.matches('[data-edit-history-url]')) return;
        const width = Math.min(270, window.innerWidth - 24);
        root.classList.toggle('is-align-end', root.getBoundingClientRect().left + width > window.innerWidth - 8);
        load(root);
    });
    document.addEventListener('dropdown:close', event => {
        if (event.target instanceof HTMLElement && event.target.matches('[data-edit-history-url]')) requests.get(event.target)?.abort();
    });
    document.addEventListener('click', event => {
        const more = event.target.closest?.('[data-edit-history-more]');
        if (more && !more.disabled && more.dataset.page) load(more.closest('[data-edit-history-url]'), Number(more.dataset.page));
    });
})();
