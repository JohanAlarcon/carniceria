const CACHE = 'carniceria-v2';
const ASSETS = ['/manifest.webmanifest', '/icons/pwa-192.png', '/icons/pwa-512.png'];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE).then((c) => c.addAll(ASSETS)).catch(() => {}));
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))),
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    // No interferir con panel, facturas ni websockets.
    if (
        url.pathname.startsWith('/admin') ||
        url.pathname.startsWith('/facturas') ||
        url.pathname.startsWith('/broadcasting') ||
        url.pathname.startsWith('/app/')
    ) {
        return;
    }

    // Assets estáticos: cache-first con revalidación.
    if (
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/images/') ||
        url.pathname.startsWith('/icons/') ||
        url.pathname === '/manifest.webmanifest'
    ) {
        event.respondWith(
            caches.open(CACHE).then(async (cache) => {
                const cached = await cache.match(request);
                const network = fetch(request)
                    .then((res) => {
                        if (res && res.status === 200) cache.put(request, res.clone());
                        return res;
                    })
                    .catch(() => cached);
                return cached || network;
            }),
        );
        return;
    }

    // Navegaciones: network-first con respaldo a la tienda cacheada.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(request).then((r) => r || caches.match('/tienda'))),
        );
    }
});
