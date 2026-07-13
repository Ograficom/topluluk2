self.addEventListener('install', (event) => {
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);
  if (url.pathname.endsWith('/manifest.json') || url.pathname.endsWith('manifest.json')) {
    event.respondWith(fetch(event.request, { cache: 'no-store' }));
    return;
  }
  if (url.pathname.startsWith('/storage/pwa/icons/')) {
    event.respondWith(fetch(event.request, { cache: 'reload' }));
    return;
  }
});
