/* Open selected local images as an unpublished Ografi post. */
(() => {
    if (!('launchQueue' in window) || typeof DataTransfer !== 'function') return;
    const input = document.getElementById('post-media');
    if (!input) return;
    const allowed = new Set(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    window.launchQueue.setConsumer(async ({files = []}) => {
        if (!files.length) return;
        const transfer = new DataTransfer();
        for (const file of Array.from(input.files || []).slice(0, 10)) transfer.items.add(file);
        let skipped = 0;
        for (const handle of files) {
            try {
                const file = await handle.getFile();
                if (!allowed.has(file.type) || file.size > 5 * 1024 * 1024 || transfer.files.length >= 10) {
                    skipped++;
                    continue;
                }
                transfer.items.add(file);
            } catch (_) { skipped++; }
        }
        if (transfer.files.length) {
            input.files = transfer.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
        if (skipped) window.alert('Bazı görseller açılamadı. En fazla 10 adet, her biri 5 MB veya daha küçük JPG, PNG, WEBP ya da GIF seçebilirsin.');
    });
})();
