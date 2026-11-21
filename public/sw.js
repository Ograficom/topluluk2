const CACHE_VERSION = 'ografi-pwa-v1';
const STATIC_CACHE = CACHE_VERSION + '-static';
const OFFLINE_URL = '/offline.html';

self.addEventListener('install', event => {
    event.waitUntil((async () => {
        const cache = await caches.open(STATIC_CACHE);
        await cache.addAll([OFFLINE_URL, '/assets/img/meta/pwa-icon.svg']);
        await self.skipWaiting();
    })());
});

self.addEventListener('activate', event => {
    event.waitUntil((async () => {
        const keys = await caches.keys();
        await Promise.all(keys.filter(key => key.startsWith('ografi-pwa-') && key !== STATIC_CACHE).map(key => caches.delete(key)));
        await self.clients.claim();
    })());
});

self.addEventListener('fetch', event => {
    const request = event.request;
    const url = new URL(request.url);
    if (request.method !== 'GET' || url.origin !== self.location.origin) return;

    // Dynamic HTML is never cached; private and authenticated pages stay network-only.
    if (request.mode === 'navigate') {
        event.respondWith((async () => {
            try { return await fetch(request); }
            catch (error) {
                const cache = await caches.open(STATIC_CACHE);
                return (await cache.match(OFFLINE_URL)) || Response.error();
            }
        })());
        return;
    }

    // Cache only same-origin static assets, never API or account responses.
    if (url.pathname.startsWith('/assets/')) {
        event.respondWith((async () => {
            const cache = await caches.open(STATIC_CACHE);
            const cached = await cache.match(request);
            if (cached) return cached;
            try {
                const response = await fetch(request);
                if (response.ok && response.type === 'basic') cache.put(request, response.clone());
                return response;
            } catch (error) { return cached || Response.error(); }
        })());
    }
});

// Preserve OgraFi's existing Web Push notifications.
self.addEventListener('push', event => {
    let payload = {};
    try { payload = event.data ? event.data.json() : {}; }
    catch (error) { payload = { title: 'OgraFi', body: event.data ? event.data.text() : 'Yeni bildirimin var.' }; }
    event.waitUntil(self.registration.showNotification(payload.title || 'OgraFi', {
        body: payload.body || 'Yeni bir bildirimin var.',
        icon: payload.icon || '/assets/img/meta/logo.svg',
        badge: '/assets/img/meta/logo.svg',
        tag: payload.tag || undefined,
        data: { url: payload.url || '/' },
        renotify: false
    }));
});

self.addEventListener('notificationclick', event => {
    event.notification.close();
    const target = new URL(event.notification.data?.url || '/', self.location.origin).href;
    event.waitUntil((async () => {
        const windows = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
        for (const client of windows) {
            if (client.url.startsWith(self.location.origin) && 'focus' in client) {
                await client.focus();
                if ('navigate' in client) await client.navigate(target);
                return;
            }
        }
        if (self.clients.openWindow) await self.clients.openWindow(target);
    })());
});
