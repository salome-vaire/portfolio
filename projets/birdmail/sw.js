self.addEventListener('install', event => {
  event.waitUntil(
    caches.open('birdmail-v1').then(cache => cache.addAll([
      './',
      './index.php',
      './app.php',
      './assets/style.css',
      './assets/app.js'
    ]))
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request).then(response => response || fetch(event.request))
  );
});
