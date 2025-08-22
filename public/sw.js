"use strict";

const CACHE_NAME = "vigilante-offline-v20"; // subí versión al cambiar
const OFFLINE_URL = "/offline.html";

const FILES_TO_CACHE = [
    OFFLINE_URL,
    "/manifest.json",
    "/logo.png",
    "/images/icons/icon-192x192.png",
    "/images/icons/icon-512x512.png",
    "/vendor/adminlte/dist/css/adminlte.min.css",
    "/vendor/adminlte/dist/js/adminlte.min.js",
    "/vendor/fontawesome-free/css/all.min.css",
];

self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(FILES_TO_CACHE))
    );
    self.skipWaiting();
});

self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) =>
                Promise.all(
                    keys.map((k) =>
                        k !== CACHE_NAME ? caches.delete(k) : null
                    )
                )
            )
    );
    self.clients.claim();
});

self.addEventListener("fetch", (event) => {
    // Ignorar mutaciones
    if (event.request.method !== "GET") return;

    const url = new URL(event.request.url);

    // Solo manejar http/https
    if (url.protocol !== "http:" && url.protocol !== "https:") {
        return; // deja que el navegador resuelva (extensiones, data:, etc.)
    }

    // Navegación HTML -> online-first con fallback offline.html
    if (event.request.mode === "navigate") {
        event.respondWith(
            fetch(event.request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    // Resto de GET -> cache-first con "cache en caliente" (solo mismo origen)
    event.respondWith(
        caches.match(event.request).then((cached) => {
            if (cached) return cached;

            return fetch(event.request)
                .then((resp) => {
                    const ct = resp.headers.get("Content-Type") || "";
                    const okToCache =
                        resp.ok &&
                        url.origin === self.location.origin && // <-- solo mismo origen
                        (ct.includes("text/") ||
                            ct.includes("image/") ||
                            ct.includes("font") ||
                            ct.includes("javascript") ||
                            ct.includes("css"));

                    if (okToCache) {
                        const copy = resp.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(event.request, copy).catch((e) => {
                                // Evita tirar el SW por errores de cache.put
                                console.debug(
                                    "No se pudo cachear:",
                                    event.request.url,
                                    e
                                );
                            });
                        });
                    }
                    return resp;
                })
                .catch(() => caches.match(OFFLINE_URL));
        })
    );
});
