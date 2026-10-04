/*
 * OVNIPORTO service worker: on purpose the smallest one that works. It precaches the
 * offline page and what it uses, and shows it when a page can't be fetched. It never
 * caches the site's HTML or data, so nobody sees stale content.
 */
const CACHE = 'ovniporto-offline-v1';
const OFFLINE_URL = '/offline.html';
const PRECACHE = [
    OFFLINE_URL,
    '/icons/icon-192.png',
    '/favicon.svg',
    '/fonts/unbounded-latin-800-normal.woff2',
    '/fonts/caveat-latin-600-normal.woff2',
    '/fonts/figtree-latin-400-normal.woff2',
];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE).then((cache) => cache.addAll(PRECACHE)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) => Promise.all(keys.filter((key) => key !== CACHE).map((key) => caches.delete(key))))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_URL)));
        return;
    }
    if (PRECACHE.includes(new URL(request.url).pathname)) {
        event.respondWith(caches.match(request).then((cached) => cached ?? fetch(request)));
    }
});
