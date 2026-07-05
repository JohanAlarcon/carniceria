// Service worker del panel de administración (PWA independiente del cliente).
// Alcance: /admin. Solo cachea iconos/manifest estáticos; el panel y Livewire
// siempre van a la red (datos frescos, sin problemas de sesión/CSRF).
const CACHE = 'carniceria-admin-v1';
const ASSETS = ['/admin-manifest.webmanifest', '/icons/pwa-192.png', '/icons/pwa-512.png'];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE).then((c) => c.addAll(ASSETS)).catch(() => {}));
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter((k) => k.startsWith('carniceria-admin') && k !== CACHE).map((k) => caches.delete(k)),
        )),
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    // Solo assets estáticos desde caché; todo lo demás va directo a la red.
    if (url.pathname.startsWith('/icons/') || url.pathname === '/admin-manifest.webmanifest') {
        event.respondWith(caches.match(request).then((cached) => cached || fetch(request)));
    }
});
