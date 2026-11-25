const CACHE_VERSION = 'ografi-pwa-v11';
const STATIC_CACHE = CACHE_VERSION + '-static';
const OFFLINE_URL = '/offline';

self.addEventListener('install', event => {
    event.waitUntil((async () => {
        const cache = await caches.open(STATIC_CACHE);
        await Promise.allSettled([OFFLINE_URL, '/assets/img/meta/pwa-icon-192.png', '/pwa/notes'].map(url => cache.add(url)));
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
                if (url.pathname === '/pwa/notes') {
                    const notePage = await cache.match('/pwa/notes');
                    if (notePage) return notePage;
                }
                return (await cache.match(OFFLINE_URL)) || new Response(
                    '<!doctype html><html lang="tr"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>OgraFi — Çevrimdışı</title><main><h1>İnternet bağlantısı yok</h1><p>Bağlantın yeniden geldiğinde sayfayı yenile.</p><a href="/">Tekrar dene</a></main></html>',
                    { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
                );
            }
        })());
        return;
    }

    if (url.pathname.startsWith('/assets/')) {
        event.respondWith((async () => {
            const cache = await caches.open(STATIC_CACHE);
            const cached = await cache.match(request);
            // Theme files can change independently of HTML and must be revalidated
            // online; otherwise a PWA can keep an incompatible mobile stylesheet.
            const isThemeCode = request.destination === 'style' || request.destination === 'script'
                || /\.(?:css|m?js)$/i.test(url.pathname);
            if (cached && !isThemeCode) return cached;
            try {
                const response = await fetch(request, isThemeCode ? { cache: 'no-cache' } : {});
                if (response.ok && response.type === 'basic') {
                    try { await cache.put(request, response.clone()); } catch (_) { /* Storage may be unavailable. */ }
                }
                if (response.status >= 500 && cached) return cached;
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
        icon: payload.icon || '/pwa/icon/192',
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


// Windows Widgets Board support. Other browsers ignore these events.
const QUICK_ACCESS_TAG = 'ografi-quick-access';
const QUICK_ACCESS_ROUTES = Object.freeze({
    'latest-posts': '/last-posts',
    'channels': '/channels',
    'create-post': '/posts/create'
});

async function renderQuickAccessWidget(widget) {
    if (!self.widgets || widget?.definition?.tag !== QUICK_ACCESS_TAG) return;
    try {
        const [template, data] = await Promise.all([
            fetch('/widgets/quick-access.json', { cache: 'no-cache' }),
            fetch('/pwa/widget-data', { credentials: 'omit', cache: 'no-cache' })
        ]);
        if (!template.ok || !data.ok) return;
        await self.widgets.updateByTag(QUICK_ACCESS_TAG, {
            template: await template.text(),
            data: await data.text()
        });
    } catch (error) {
        console.warn('OgraFi widget güncellenemedi:', error);
    }
}

self.addEventListener('widgetinstall', event => {
    event.waitUntil((async () => {
        await renderQuickAccessWidget(event.widget);
        if (event.widget?.definition?.tag !== QUICK_ACCESS_TAG || !self.registration.periodicSync) return;
        try {
            const tags = await self.registration.periodicSync.getTags();
            if (!tags.includes(QUICK_ACCESS_TAG)) {
                await self.registration.periodicSync.register(QUICK_ACCESS_TAG, { minInterval: 60 * 60 * 1000 });
            }
        } catch (_) { /* Host or browser may deny background sync. */ }
    })());
});
self.addEventListener('widgetuninstall', event => {
    if (event.widget?.definition?.tag !== QUICK_ACCESS_TAG || !self.registration.periodicSync) return;
    if (event.widget.instances?.length > 1) return;
    event.waitUntil(self.registration.periodicSync.unregister(QUICK_ACCESS_TAG).catch(() => {}));
});
self.addEventListener('widgetresume', event => {
    event.waitUntil(renderQuickAccessWidget(event.widget));
});
self.addEventListener('activate', event => {
    if (!self.widgets?.getByTag) return;
    event.waitUntil((async () => {
        try {
            await renderQuickAccessWidget(await self.widgets.getByTag(QUICK_ACCESS_TAG));
        } catch (error) {
            console.warn('OgraFi widget yüklenemedi:', error);
        }
    })());
});
self.addEventListener('widgetclick', event => {
    if (!Object.prototype.hasOwnProperty.call(QUICK_ACCESS_ROUTES, event.action)) return;
    const target = new URL(QUICK_ACCESS_ROUTES[event.action], self.location.origin).href;
    event.waitUntil(self.clients.openWindow(target));
});


// Refresh public, non-personalized offline assets. Never replay authenticated
// mutations, publish drafts or cache private feeds in the background.
const PUBLIC_ASSET_SYNC = 'ografi-public-assets';
const PUBLIC_OFFLINE_ASSETS = [
    '/offline',
    '/assets/img/meta/pwa-icon-192.png',
    '/widgets/quick-access.json',
    '/pwa/widget-data',
    '/pwa/notes'
];
async function refreshPublicOfflineAssets() {
    const cache = await caches.open(STATIC_CACHE);
    await Promise.all(PUBLIC_OFFLINE_ASSETS.map(async path => {
        const response = await fetch(path, { credentials: 'omit', cache: 'no-cache', redirect: 'error' });
        if (!response.ok) throw new Error('Public asset refresh failed: ' + path);
        await cache.put(path, response);
    }));
    if (self.widgets?.getByTag) {
        const widget = await self.widgets.getByTag(QUICK_ACCESS_TAG);
        if (widget) await renderQuickAccessWidget(widget);
    }
}
self.addEventListener('message', event => {
    if (event.data?.type !== 'ografi-refresh-public-assets') return;
    event.waitUntil(refreshPublicOfflineAssets().catch(async () => {
        if (self.registration.sync) {
            try { await self.registration.sync.register(PUBLIC_ASSET_SYNC); } catch (_) {}
        }
    }));
});
self.addEventListener('sync', event => {
    if (event.tag === PUBLIC_ASSET_SYNC) event.waitUntil(refreshPublicOfflineAssets());
});
self.addEventListener('periodicsync', event => {
    if (event.tag === PUBLIC_ASSET_SYNC) {
        event.waitUntil(refreshPublicOfflineAssets());
    } else if (event.tag === QUICK_ACCESS_TAG && self.widgets?.getByTag) {
        event.waitUntil((async () => {
            const widget = await self.widgets.getByTag(QUICK_ACCESS_TAG);
            if (widget) await renderQuickAccessWidget(widget);
        })());
    }
});
