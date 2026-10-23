const CACHE_PREFIX = 'journal-public-shell:' + self.registration.scope + ':';
const PUBLIC_CACHE = CACHE_PREFIX + 'v1';
const OFFLINE_URL = new URL('offline.html', self.registration.scope).href;

self.addEventListener('install', event => {
    event.waitUntil(caches.open(PUBLIC_CACHE).then(cache => cache.add(new Request(OFFLINE_URL, {
        cache: 'reload', credentials: 'omit'
    }))).then(() => self.skipWaiting()));
});

self.addEventListener('activate', event => {
    event.waitUntil(caches.keys().then(keys => Promise.all(keys.filter(key =>
        key.startsWith(CACHE_PREFIX) && key !== PUBLIC_CACHE
    ).map(key => caches.delete(key)))).then(() => self.clients.claim()));
});

self.addEventListener('fetch', event => {
    const request = event.request;
    const url = new URL(request.url);
    if (request.method !== 'GET' || request.mode !== 'navigate' || url.origin !== self.location.origin || !url.href.startsWith(self.registration.scope)) return;

    // Never persist authenticated pages, JSON, decrypted media, exports or form
    // submissions. The only Cache Storage entry is the public offline notice.
    event.respondWith(fetch(request, { cache: 'no-store' }).catch(() => caches.match(OFFLINE_URL)));
});
