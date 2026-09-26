import { onCLS, onFCP, onINP, onLCP, onTTFB } from 'web-vitals';

const sendMetrics = (metrics) => {
    if (!metrics.length) return;

    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const payload = JSON.stringify({ metrics });

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
        body: payload,
    }).catch(() => {});
};

const initWebVitals = async () => {
    if (!window.isSecureContext || !window.fetch) return;

    let webVitals;
    try {
        webVitals = await loadWebVitals();
    } catch {
        return;
    }

    if (!webVitals) return;

    const pending = new Map();
    let sendTimer = null;
    let sent = false;

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

    const flush = () => {
        if (!pending.size) return;
        const metrics = Array.from(pending.values());
        pending.clear();
        sent = true;
        sendMetrics(metrics);
    };

    webVitals.onLCP(queueMetric);
    webVitals.onINP(queueMetric);
    webVitals.onCLS(queueMetric);
    webVitals.onFCP(queueMetric);
    webVitals.onTTFB(queueMetric);

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
