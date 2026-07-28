const CACHE_NAME = 'hazina-cache-v4';

const ASSETS_TO_CACHE = [
    'assets/css/bootstrap.min.css',
    'assets/css/style.css',
    'assets/css/hf-theme.css',
    'assets/js/jquery.min.js',
    'assets/img/icons/icon-192.png',
    'assets/img/icons/icon-512.png'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(ASSETS_TO_CACHE))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;

    // Les pages PHP (.php ou sans extension) : toujours demandées au serveur en direct,
    // jamais servies depuis le cache (elles contiennent des données dynamiques comme la session)
    const url = new URL(event.request.url);
    if (url.pathname.endsWith('.php') || event.request.mode === 'navigate') {
        event.respondWith(fetch(event.request));
        return;
    }

    // Le reste (CSS/JS/images) : cache d'abord, réseau en secours
    event.respondWith(
        caches.match(event.request).then((cached) => cached || fetch(event.request))
    );
});