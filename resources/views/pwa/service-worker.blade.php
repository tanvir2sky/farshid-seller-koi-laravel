const APP_CACHE_VERSION = 'seller-koi-pwa-v1';
const STATIC_CACHE = `${APP_CACHE_VERSION}-static`;
const PAGE_CACHE = `${APP_CACHE_VERSION}-pages`;
const OFFLINE_URL = @json(route('pwa.offline'));
const PRECACHE_URLS = [
    OFFLINE_URL,
    @json(url('/')),
    @json(asset('pwa/icons/icon-192.png')),
    @json(asset('pwa/icons/icon-512.png')),
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches
            .open(STATIC_CACHE)
            .then((cache) => cache.addAll(PRECACHE_URLS.map((url) => new Request(url, { cache: 'reload' }))))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) =>
                Promise.all(
                    keys
                        .filter((key) => !key.startsWith(APP_CACHE_VERSION))
                        .map((key) => caches.delete(key))
                )
            )
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    if (url.pathname.startsWith('/admin') || url.pathname.startsWith('/api')) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(handleNavigationRequest(request));
        return;
    }

    if (['style', 'script', 'image', 'font', 'worker'].includes(request.destination)) {
        event.respondWith(handleAssetRequest(request));
    }
});

async function handleNavigationRequest(request) {
    const cache = await caches.open(PAGE_CACHE);

    try {
        const response = await fetch(request);

        if (response && response.ok) {
            cache.put(request, response.clone());
        }

        return response;
    } catch (error) {
        const cachedPage = await cache.match(request);

        if (cachedPage) {
            return cachedPage;
        }

        const precachedHomepage = await caches.match(@json(url('/')));

        if (isHomepageRequest(request) && precachedHomepage) {
            return precachedHomepage;
        }

        return caches.match(OFFLINE_URL);
    }
}

async function handleAssetRequest(request) {
    const cache = await caches.open(STATIC_CACHE);
    const cachedAsset = await cache.match(request);

    const networkFetch = fetch(request)
        .then((response) => {
            if (response && response.ok) {
                cache.put(request, response.clone());
            }

            return response;
        })
        .catch(() => null);

    if (cachedAsset) {
        return cachedAsset;
    }

    const networkAsset = await networkFetch;

    return networkAsset || Response.error();
}

function isHomepageRequest(request) {
    const requestUrl = new URL(request.url);
    const homepageUrl = new URL(@json(url('/')));

    return requestUrl.origin === homepageUrl.origin && requestUrl.pathname === homepageUrl.pathname;
}
