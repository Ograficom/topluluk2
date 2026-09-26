import { onCLS, onFCP, onINP, onLCP, onTTFB } from 'web-vitals';

/*
 * Ografi gerçek kullanıcı performansını (RUM) toplar.
 * Ölçüm kodu idle sonrasına bırakılır; sayfa açılışını gereksiz yere bloke etmez.
 * Yalnızca anonim teknik metrikler gönderilir.
 */

const endpoint = '/telemetry/web-vitals';

const sendMetrics = (metrics) => {
    if (!metrics.length) return;

    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';

    fetch(endpoint, {
        method: 'POST',
        credentials: 'same-origin',
        keepalive: true,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ metrics }),
    }).catch(() => {});
};

const initWebVitals = () => {
    if (!window.isSecureContext || !window.fetch) return;

    const pending = new Map();
    let sendTimer = null;

    const flush = () => {
        if (!pending.size) return;

        const metrics = Array.from(pending.values());
        pending.clear();
        sendMetrics(metrics);
    };

    const queueMetric = (metric) => {
        pending.set(metric.name, {
            name: metric.name,
            value: Number(metric.value),
            page: window.location.href,
            route: document.body?.dataset?.routeName || null,
            device: window.matchMedia('(max-width: 767px)').matches ? 'mobile' : 'desktop',
            connection: navigator.connection?.effectiveType || null,
            navigation: performance.getEntriesByType('navigation')[0]?.type || null,
        });

        if (sendTimer) window.clearTimeout(sendTimer);
        sendTimer = window.setTimeout(flush, 1200);
    };

    onLCP(queueMetric);
    onINP(queueMetric);
    onCLS(queueMetric);
    onFCP(queueMetric);
    onTTFB(queueMetric);

    window.addEventListener('pagehide', flush, { once: true });

    // LCP/FCP/TTFB çoğu sayfada hızlıca gelir; INP/CLS daha sonra kesinleşebilir.
    window.setTimeout(flush, 9000);
};

const schedule = window.requestIdleCallback
    ? (cb) => window.requestIdleCallback(cb, { timeout: 5000 })
    : (cb) => window.setTimeout(cb, 2500);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => schedule(initWebVitals), { once: true });
} else {
    schedule(initWebVitals);
}
