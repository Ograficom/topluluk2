const CACHE_VERSION = 'ografi-pwa-v2';
const STATIC_CACHE = CACHE_VERSION + '-static';
const OFFLINE_URL = '/offline';

self.addEventListener('install', event => {
    event.waitUntil((async () => {
        const cache = await caches.open(STATIC_CACHE);
        await Promise.allSettled([OFFLINE_URL, '/assets/img/meta/pwa-icon.svg'].map(url => cache.add(url)));
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
    const destination = new URL(event.notification.data?.url || '/', self.location.origin);
    const target = destination.origin === self.location.origin ? destination.href : self.location.origin + '/';
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
