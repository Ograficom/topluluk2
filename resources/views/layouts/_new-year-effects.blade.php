<script>
(() => {
    const options = {{ \Illuminate\Support\Js::from(['snow' => (bool) $newYearSettings['snow'], 'confetti' => (bool) $newYearSettings['confetti'], 'target' => \App\Support\NewYearMode::target($newYearSettings)->getTimestamp() * 1000]) }};
    const card = document.querySelector('[data-ny-card]');
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    let snowLayer = null;
    let celebrationLayer = null;
    let celebrated = false;
    function celebrate() {
        if (!options.confetti || celebrated || reduced.matches || document.hidden) return;
        const now = Date.now();
        if (now < options.target || now >= options.target + 86400000) return;
        celebrated = true;
        const key = 'ografi-new-year-celebration-' + options.target;
        try {
            if (sessionStorage.getItem(key)) return;
            sessionStorage.setItem(key, '1');
        } catch (_) {}
        const layer = document.createElement('div');
        layer.className = 'ografi-ny-layer';
        layer.setAttribute('aria-hidden', 'true');
        const colors = ['#6cbaff','#f9d86d','#fa7b86','#72d9b2','#ffffff'];
        for (let i=0; i<36; i++) {
            const flake = document.createElement('i');
            flake.className = 'ografi-ny-confetti';
            flake.style.cssText = '--x:'+Math.random()*100+'%;--duration:'+(2+Math.random()*2)+'s;--delay:'+Math.random()*.65+'s;--drift:'+(Math.random()*160-80)+'px;--color:'+colors[i%colors.length];
            layer.append(flake);
        }
        document.body.append(layer);
        celebrationLayer = layer;
        window.setTimeout(() => { layer.remove(); celebrationLayer = null; }, 5000);
    }
    function update() {
        const seconds = Math.max(0, Math.ceil((options.target-Date.now())/1000));
        if (card) {
            const nums = [Math.floor(seconds/86400),Math.floor(seconds/3600)%24,Math.floor(seconds/60)%60,seconds%60];
            card.querySelectorAll('[data-ny-number]').forEach((node,i) => { node.textContent = String(nums[i]).padStart(2,'0'); });
            if (!seconds) {
                card.querySelector('[data-ny-heading]').textContent = 'Mutlu yıllar!';
                card.querySelector('[data-ny-clock]').hidden = true;
            }
        }
        celebrate();
    }
    function syncSnow() {
        if (!options.snow || reduced.matches || document.hidden) {
            snowLayer?.remove(); snowLayer = null;
            if (reduced.matches) { celebrationLayer?.remove(); celebrationLayer = null; }
            return;
        }
        if (snowLayer) return;
        snowLayer = document.createElement('div');
        snowLayer.className = 'ografi-ny-layer';
        snowLayer.setAttribute('aria-hidden','true');
        const count = window.matchMedia('(max-width: 600px)').matches ? 14 : 26;
        for (let i=0; i<count; i++) {
            const flake = document.createElement('i');
            flake.className = 'ografi-ny-flake';
            flake.style.cssText = '--x:'+Math.random()*100+'%;--size:'+(2+Math.random()*3)+'px;--duration:'+(8+Math.random()*8)+'s;--delay:-'+Math.random()*15+'s;--drift:'+(Math.random()*100-50)+'px';
            snowLayer.append(flake);
        }
        document.body.append(snowLayer);
    }
    let timer = null;
    function sync() {
        syncSnow();
        window.clearInterval(timer);
        if (!document.hidden) {
            update();
            if (card || options.confetti) timer = window.setInterval(update,1000);
        }
    }
    document.addEventListener('visibilitychange',sync);
    reduced.addEventListener?.('change',sync);
    sync();
})();
</script>
