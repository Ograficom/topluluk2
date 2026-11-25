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
            positionMenu(root);
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
    function positionMenu(root) {
        const menu = root.querySelector('.post-edit-history-menu');
        if (!menu) return;
        const rect = root.getBoundingClientRect();
        const width = menu.offsetWidth || Math.min(270, window.innerWidth - 24);
        // Keep the leading edge under the clock whenever there is room;
        // shift just enough to keep the complete menu inside the viewport.
        const viewportWidth = document.documentElement.clientWidth;
        const left = Math.max(12, Math.min(rect.left, viewportWidth - width - 12));
        root.style.setProperty('--history-menu-left', (left - rect.left) + 'px');
        if (window.matchMedia('(max-width:720px)').matches) {
            const height = document.documentElement.clientHeight;
            const menuHeight = Math.min(menu.scrollHeight || 100, height - 24);
            const below = rect.bottom + 7;
            const top = below + menuHeight <= height - 12
                ? below : Math.max(12, Math.min(rect.top - menuHeight - 7, height - menuHeight - 12));
            root.style.setProperty('--history-viewport-left', left + 'px');
            root.style.setProperty('--history-viewport-top', Math.max(12, top) + 'px');
            root.style.setProperty('--history-viewport-height', Math.max(48, height - Math.max(12, top) - 12) + 'px');
        }
    }
    let positionFrame = 0;
    function schedulePosition() {
        if (positionFrame) return;
        positionFrame = requestAnimationFrame(() => {
            positionFrame = 0;
            document.querySelectorAll('[data-edit-history-url].is-open').forEach(positionMenu);
        });
    }
    window.addEventListener('resize', schedulePosition, {passive:true});
    window.addEventListener('scroll', schedulePosition, {passive:true, capture:true});
    document.addEventListener('dropdown:open', event => {
        const root = event.target;
        if (!(root instanceof HTMLElement) || !root.matches('[data-edit-history-url]')) return;
        positionMenu(root);
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
