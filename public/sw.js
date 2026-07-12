self.addEventListener('install', () => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const cacheNames = await caches.keys();

        await Promise.all(cacheNames.map((cacheName) => caches.delete(cacheName)));
        await self.clients.claim();

        const clients = await self.clients.matchAll({
            type: 'window',
            includeUncontrolled: true,
        });

        await self.registration.unregister();

        for (const client of clients) {
            client.navigate(client.url);
        }
    })());
});
