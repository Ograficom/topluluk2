(() => {
    'use strict';
    const radians = Math.PI / 180;
    const half = 140 * radians;
    const clamp = (value, min, max) => Math.max(min, Math.min(max, value));
    document.addEventListener('click', event => {
        const trigger = event.target instanceof Element && event.target.closest('a[data-ografi-panorama]');
        if (!trigger || event.defaultPrevented || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        const dialog = document.createElement('dialog');
        if (typeof dialog.showModal !== 'function') return;
        event.preventDefault();
        dialog.className = 'ografi-panorama-dialog';
        dialog.setAttribute('aria-label', '280 derece panorama');
        dialog.innerHTML = '<header><strong>280° Keşfet</strong><button type="button" data-expand aria-label="Tam ekran">⛶</button><button type="button" data-close aria-label="Kapat">✕</button></header><canvas tabindex="0" aria-label="Panorama. Ok tuşlarıyla gezinin." hidden></canvas><div class="ografi-panorama-controls" hidden><button type="button" data-minus aria-label="Uzaklaştır">−</button><button type="button" data-plus aria-label="Yakınlaştır">+</button><input type="range" min="-140" max="140" step="1" value="0" aria-label="Yatay görüş açısı"><button type="button" data-reset aria-label="Görünümü sıfırla">↺</button></div><p class="ografi-panorama-status" role="status">Panorama yükleniyor…</p><p class="ografi-panorama-status"><a data-original target="_blank" rel="noopener noreferrer">Orijinal fotoğrafı aç</a></p>';
        document.body.append(dialog);
        const canvas = dialog.querySelector('canvas');
        const context = canvas.getContext('2d', {alpha:false});
        const controls = dialog.querySelector('.ografi-panorama-controls');
        const status = dialog.querySelector('[role="status"]');
        const range = dialog.querySelector('input');
        const image = new Image();
        dialog.querySelector('[data-original]').href = trigger.href;
        let yaw = 0, fov = 80 * radians, frame = 0, pointer = null, lastX = 0, ready = false;
        const draw = () => {
            frame = 0;
            if (!ready || !dialog.open) return;
            const rect = canvas.getBoundingClientRect();
            if (!rect.width || !rect.height) return;
            const scale = Math.min(1.5, 960 / rect.width);
            const w = Math.max(1, Math.round(rect.width * scale));
            const h = Math.max(1, Math.round(rect.height * scale));
            if (canvas.width !== w) canvas.width = w;
            if (canvas.height !== h) canvas.height = h;
            const limit = half - fov / 2;
            yaw = clamp(yaw, -limit, limit);
            range.min = String(Math.ceil(-limit / radians));
            range.max = String(Math.floor(limit / radians));
            range.value = String(Math.round(yaw / radians));
            const focal = w / (2 * Math.tan(fov / 2));
            const pixelsPerRadian = image.naturalWidth / (half * 2);
            context.fillStyle = '#111';
            context.fillRect(0, 0, w, h);
            const step = 2;
            for (let x = 0; x < w; x += step) {
                const end = Math.min(w, x + step);
                const a = Math.atan((x - w / 2) / focal);
                const b = Math.atan((end - w / 2) / focal);
                const angle = (a + b) / 2;
                const sx = clamp((yaw + a + half) * pixelsPerRadian, 0, image.naturalWidth - 1);
                const sw = Math.min(image.naturalWidth - sx, Math.max(1, (b - a) * pixelsPerRadian));
                const projectedHeight = image.naturalHeight * focal / (pixelsPerRadian * Math.cos(angle));
                context.drawImage(image, sx, 0, sw, image.naturalHeight, x, (h - projectedHeight) / 2, end - x, projectedHeight);
            }
        };
        const render = () => { if (!frame) frame = requestAnimationFrame(draw); };
        const zoom = direction => { fov = clamp(fov + direction * 10 * radians, 35 * radians, 100 * radians); render(); };
        const resize = typeof ResizeObserver === 'function' ? new ResizeObserver(render) : null;
        canvas.addEventListener('pointerdown', e => {
            if (pointer !== null || e.button !== 0) return;
            pointer = e.pointerId; lastX = e.clientX; canvas.setPointerCapture(pointer);
        });
        canvas.addEventListener('pointermove', e => {
            if (e.pointerId !== pointer) return;
            yaw -= (e.clientX - lastX) * fov / Math.max(1, canvas.clientWidth);
            lastX = e.clientX; render();
        });
        const release = e => { if (pointer === e.pointerId) pointer = null; };
        ['pointerup','pointercancel','lostpointercapture'].forEach(name => canvas.addEventListener(name, release));
        canvas.addEventListener('keydown', e => {
            if (!['ArrowLeft','ArrowRight','+','-','Home'].includes(e.key)) return;
            e.preventDefault();
            if (e.key === 'ArrowLeft') yaw -= 8 * radians;
            if (e.key === 'ArrowRight') yaw += 8 * radians;
            if (e.key === '+') zoom(-1);
            if (e.key === '-') zoom(1);
            if (e.key === 'Home') { yaw = 0; fov = 80 * radians; }
            render();
        });
        range.addEventListener('input', () => { yaw = Number(range.value) * radians; render(); });
        dialog.querySelector('[data-minus]').addEventListener('click', () => zoom(1));
        dialog.querySelector('[data-plus]').addEventListener('click', () => zoom(-1));
        dialog.querySelector('[data-reset]').addEventListener('click', () => { yaw = 0; fov = 80 * radians; render(); });
        dialog.querySelector('[data-expand]').addEventListener('click', e => {
            const expanded = dialog.classList.toggle('is-expanded');
            e.currentTarget.setAttribute('aria-pressed', String(expanded)); render();
        });
        dialog.querySelector('[data-close]').addEventListener('click', () => dialog.close());
        dialog.addEventListener('close', () => {
            ready = false; image.onload = image.onerror = null; image.src = '';
            if (frame) cancelAnimationFrame(frame);
            resize?.disconnect(); window.removeEventListener('resize', render);
            dialog.remove(); trigger.focus();
        }, {once:true});
        try { dialog.showModal(); } catch (_) { dialog.remove(); location.href = trigger.href; return; }
        if (!context) { status.textContent = 'Tarayıcınız panorama görüntüleyicisini desteklemiyor.'; return; }
        image.onload = () => {
            if (!dialog.open) return;
            if (image.naturalWidth < image.naturalHeight * 2) {
                status.textContent = 'Bu fotoğraf yeterince geniş değil. Orijinal fotoğrafı açabilirsiniz.'; return;
            }
            ready = true; canvas.hidden = false; controls.hidden = false;
            status.textContent = 'Sürükleyerek veya ok tuşlarıyla gezinin. Yaklaşık 280° yatay görünüm.';
            resize?.observe(canvas); window.addEventListener('resize', render); render();
        };
        image.onerror = () => { status.textContent = 'Panorama yüklenemedi. Orijinal fotoğrafı açmayı deneyin.'; };
        image.src = trigger.href;
    });
})();
