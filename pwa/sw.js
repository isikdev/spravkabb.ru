const CACHE_NAME = 'bukihi-cache-v2';
const STATIC_CACHE_NAME = 'bukihi-static-v2';
const DYNAMIC_CACHE_NAME = 'bukihi-dynamic-v2';
const MAX_DYNAMIC_CACHE_ITEMS = 30; // Ограничиваем количество кешированных элементов

const STATIC_ASSETS = [
    '/',
    '/index.php',
    '/add.php',
    '/login.php',
    '/pwa/manifest.json',
    '/pwa/pwa.js',
    '/pwa/install.js',
    '/img/logo.jpg',
    '/pwa/icons/icon-72x72.png',
    '/pwa/icons/icon-96x96.png',
    '/pwa/icons/icon-128x128.png',
    '/pwa/icons/icon-144x144.png',
    '/pwa/icons/icon-152x152.png',
    '/pwa/icons/icon-192x192.png',
    '/pwa/icons/icon-384x384.png',
    '/pwa/icons/icon-512x512.png',
    'https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&display=swap',
    'https://cdn.tailwindcss.com',
    '/pwa/offline.html'
];

// Функция для ограничения размера кеша
async function trimCache(cacheName, maxItems) {
    const cache = await caches.open(cacheName);
    const keys = await cache.keys();

    if (keys.length > maxItems) {
        // Удаляем самые старые записи, оставляя maxItems элементов
        await cache.delete(keys[0]);
        // Рекурсивно вызываем функцию, если всё ещё превышаем лимит
        await trimCache(cacheName, maxItems);
    }
}

// Установка service worker
self.addEventListener('install', event => {
    console.log('[Service Worker] Установка');
    event.waitUntil(
        caches.open(STATIC_CACHE_NAME)
            .then(cache => {
                console.log('[Service Worker] Предварительное кеширование статических ресурсов');
                return cache.addAll(STATIC_ASSETS);
            })
            .catch(error => {
                console.error('[Service Worker] Ошибка кеширования:', error);
            })
    );

    // Принудительно активируем Service Worker без ожидания закрытия
    self.skipWaiting();
});

// Активация service worker
self.addEventListener('activate', event => {
    console.log('[Service Worker] Активация');
    const currentCaches = [STATIC_CACHE_NAME, DYNAMIC_CACHE_NAME];

    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                return Promise.all(
                    cacheNames.map(cacheName => {
                        if (!currentCaches.includes(cacheName)) {
                            console.log('[Service Worker] Удаление старого кеша:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('[Service Worker] Активирован');
                return self.clients.claim();
            })
    );
});

// Оптимизированная стратегия кеширования
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Пропускаем POST запросы и запросы к API
    if (event.request.method !== 'GET') {
        return;
    }

    // Не кешируем админ-панель и динамические запросы PHP с параметрами
    if (url.pathname.includes('/admin.php') ||
        (url.pathname.endsWith('.php') && url.search !== '')) {
        return fetch(event.request);
    }

    // Отдельная стратегия для изображений из папки uploads
    if (url.pathname.includes('/uploads/')) {
        event.respondWith(networkWithCacheFallback(event.request));
        return;
    }

    // Стандартная стратегия - кеш с обновлением по сети
    event.respondWith(
        caches.match(event.request)
            .then(cachedResponse => {
                // Если есть в кеше, возвращаем из кеша, но обновляем в фоне
                if (cachedResponse) {
                    // Обновляем кеш в фоне
                    fetch(event.request)
                        .then(response => {
                            if (response.ok) {
                                updateCache(event.request, response.clone());
                            }
                        })
                        .catch(() => { });

                    return cachedResponse;
                }

                // Если нет в кеше, получаем из сети и кешируем
                return fetch(event.request)
                    .then(response => {
                        if (!response || response.status !== 200) {
                            return response;
                        }

                        return updateCache(event.request, response.clone())
                            .then(() => {
                                return response;
                            });
                    })
                    .catch(error => {
                        console.log('[Service Worker] Ошибка сети:', error);

                        // Для HTML-запросов возвращаем страницу офлайн
                        if (event.request.headers.get('accept').includes('text/html')) {
                            return caches.match('/pwa/offline.html');
                        }

                        return new Response('Offline mode');
                    });
            })
    );
});

// Функция для обновления кеша с ограничением размера
async function updateCache(request, response) {
    // Не кешируем большие файлы (более 1МБ)
    const contentLength = response.headers.get('content-length');
    if (contentLength && parseInt(contentLength) > 1024 * 1024) {
        return Promise.resolve();
    }

    const cache = await caches.open(DYNAMIC_CACHE_NAME);
    await cache.put(request, response);

    // Ограничиваем размер кеша
    return trimCache(DYNAMIC_CACHE_NAME, MAX_DYNAMIC_CACHE_ITEMS);
}

// Стратегия для изображений - сначала сеть, затем кеш
function networkWithCacheFallback(request) {
    return fetch(request)
        .then(response => {
            if (!response || response.status !== 200) {
                return response;
            }

            const clonedResponse = response.clone();
            caches.open(DYNAMIC_CACHE_NAME)
                .then(cache => {
                    cache.put(request, clonedResponse);
                    trimCache(DYNAMIC_CACHE_NAME, MAX_DYNAMIC_CACHE_ITEMS);
                });

            return response;
        })
        .catch(() => {
            return caches.match(request);
        });
} 