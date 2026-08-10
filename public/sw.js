/**
 * Charger log service worker.
 *
 * Bump VERSION whenever this file or the precache list changes; the activate
 * handler then drops every cache that doesn't belong to the new version.
 */
const VERSION = 'v1';

const PRECACHE = `charger-precache-${VERSION}`;
const RUNTIME = `charger-runtime-${VERSION}`;

const OFFLINE_URL = '/offline.html';

const PRECACHE_URLS = [
    OFFLINE_URL,
    '/icons/icon.svg',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/apple-touch-icon.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches
            .open(PRECACHE)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => key !== PRECACHE && key !== RUNTIME)
                    .map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    // Never interfere with form submissions, deletes, or the Vite dev server.
    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(networkFirst(request));

        return;
    }

    if (isCacheableAsset(url)) {
        event.respondWith(cacheFirst(request));
    }
});

/**
 * Assets that are safe to serve from the cache indefinitely: Vite emits
 * content-hashed filenames under /build, and the icons never change in place.
 */
function isCacheableAsset(url) {
    return url.pathname.startsWith('/build/')
        || url.pathname.startsWith('/icons/')
        || url.pathname === '/favicon.ico';
}

/**
 * Pages are always fetched fresh — they carry flash messages and CSRF tokens,
 * so a stale copy would be worse than an honest offline screen.
 */
async function networkFirst(request) {
    try {
        return await fetch(request);
    } catch {
        const cache = await caches.open(PRECACHE);

        return (await cache.match(OFFLINE_URL)) ?? Response.error();
    }
}

async function cacheFirst(request) {
    const cached = await caches.match(request);

    if (cached) {
        return cached;
    }

    const response = await fetch(request);

    if (response.ok) {
        const cache = await caches.open(RUNTIME);

        cache.put(request, response.clone());
    }

    return response;
}
