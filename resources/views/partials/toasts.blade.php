<div id="toast-root" class="pointer-events-none fixed right-4 top-4 z-[9999] flex w-full max-w-sm flex-col gap-3">
    <div
        id="toast-success"
        class="pointer-events-auto hidden flex items-center w-full p-4 text-slate-800 bg-white/90 rounded-2xl shadow-[0_18px_50px_-24px_rgba(15,23,42,0.6)] border border-slate-200/70 backdrop-blur-lg ring-1 ring-black/5"
        role="alert"
        data-toast="success"
    >
        <div class="inline-flex items-center justify-center shrink-0 w-8 h-8 text-emerald-700 bg-emerald-50 rounded-xl ring-1 ring-emerald-100">
            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11.917 9.724 16.5 19 7.5"/></svg>
            <span class="sr-only">Basari ikonu</span>
        </div>
        <div class="ms-3 text-sm font-medium text-slate-700" data-toast-message>Islem basariyla tamamlandi.</div>
        <button type="button" class="ms-auto flex items-center justify-center text-slate-500 hover:text-slate-700 bg-transparent border border-transparent hover:bg-slate-100 focus:ring-4 focus:ring-slate-200 font-medium leading-5 rounded-xl text-sm h-8 w-8 focus:outline-none transition" data-dismiss-target="#toast-success" aria-label="Kapat">
            <span class="sr-only">Kapat</span>
            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/></svg>
        </button>
    </div>

    <div
        id="toast-danger"
        class="pointer-events-auto hidden flex items-center w-full p-4 text-slate-800 bg-white/90 rounded-2xl shadow-[0_18px_50px_-24px_rgba(15,23,42,0.6)] border border-slate-200/70 backdrop-blur-lg ring-1 ring-black/5"
        role="alert"
        data-toast="danger"
    >
        <div class="inline-flex items-center justify-center shrink-0 w-8 h-8 text-rose-700 bg-rose-50 rounded-xl ring-1 ring-rose-100">
            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/></svg>
            <span class="sr-only">Hata ikonu</span>
        </div>
        <div class="ms-3 text-sm font-medium text-slate-700" data-toast-message>Oge silindi.</div>
        <button type="button" class="ms-auto flex items-center justify-center text-slate-500 hover:text-slate-700 bg-transparent border border-transparent hover:bg-slate-100 focus:ring-4 focus:ring-slate-200 font-medium leading-5 rounded-xl text-sm h-8 w-8 focus:outline-none transition" data-dismiss-target="#toast-danger" aria-label="Kapat">
            <span class="sr-only">Kapat</span>
            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/></svg>
        </button>
    </div>

    <div
        id="toast-warning"
        class="pointer-events-auto hidden flex items-center w-full p-4 text-slate-800 bg-white/90 rounded-2xl shadow-[0_18px_50px_-24px_rgba(15,23,42,0.6)] border border-slate-200/70 backdrop-blur-lg ring-1 ring-black/5"
        role="alert"
        data-toast="warning"
    >
        <div class="inline-flex items-center justify-center shrink-0 w-8 h-8 text-amber-700 bg-amber-50 rounded-xl ring-1 ring-amber-100">
            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V8m0 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            <span class="sr-only">Uyari ikonu</span>
        </div>
        <div class="ms-3 text-sm font-medium text-slate-700" data-toast-message>Sifre guvenligini guclendirin.</div>
        <button type="button" class="ms-auto flex items-center justify-center text-slate-500 hover:text-slate-700 bg-transparent border border-transparent hover:bg-slate-100 focus:ring-4 focus:ring-slate-200 font-medium leading-5 rounded-xl text-sm h-8 w-8 focus:outline-none transition" data-dismiss-target="#toast-warning" aria-label="Kapat">
            <span class="sr-only">Kapat</span>
            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/></svg>
        </button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const root = document.getElementById('toast-root');
        if (!root) return;

        const timers = {};
        const ids = {
            success: 'toast-success',
            danger: 'toast-danger',
            warning: 'toast-warning'
        };

        const hideToast = (el) => {
            if (!el) return;
            el.classList.add('hidden');
        };

        root.addEventListener('click', (event) => {
            const btn = event.target.closest('[data-dismiss-target]');
            if (!btn) return;
            const selector = btn.getAttribute('data-dismiss-target');
            if (!selector) return;
            hideToast(document.querySelector(selector));
        });

        window.showToast = (type, message, options = {}) => {
            const key = ids[type] ? type : 'warning';
            const el = document.getElementById(ids[key]);
            if (!el) return;
            const msgEl = el.querySelector('[data-toast-message]');
            if (msgEl && typeof message === 'string') msgEl.textContent = message;
            el.classList.remove('hidden');

            const timeout = Number.isFinite(options.timeout) ? options.timeout : 3000;
            if (timers[key]) clearTimeout(timers[key]);
            timers[key] = setTimeout(() => hideToast(el), timeout);
        };
    });
</script>

