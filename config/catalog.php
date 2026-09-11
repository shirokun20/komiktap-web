<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Katalog v2 (proxy resmi komiktap.info)
    |--------------------------------------------------------------------------
    |
    | Proxy ke REST resmi WordPress, bukan parsing HTML.
    | Upstream: https://komiktap.info/wp-json/v2/komiktap
    |
    */

    'upstream_base_url' => env('CATALOG_UPSTREAM_BASE_URL', 'https://komiktap.info/wp-json/v2/komiktap'),

    'user_agent' => env(
        'CATALOG_USER_AGENT',
        'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36'
    ),

    'referer' => env('CATALOG_REFERER', 'https://komiktap.info/'),

    'timeout' => (int) env('CATALOG_TIMEOUT', 10),

    'retry_times' => (int) env('CATALOG_RETRY_TIMES', 1),
    'retry_sleep_ms' => (int) env('CATALOG_RETRY_SLEEP_MS', 200),

    'ttl' => [
        'list' => (int) env('CATALOG_TTL_LIST', 600), // 10 menit
        'detail' => (int) env('CATALOG_TTL_DETAIL', 3600), // 60 menit
        'announcements' => (int) env('CATALOG_TTL_ANNOUNCEMENTS', 900), // 15 menit
        'genres' => (int) env('CATALOG_TTL_GENRES', 86400), // 24 jam (genre + A-Z jarang berubah)
    ],

    // Basis halaman situs untuk genre index + A-Z list (HTML).
    'site_base_url' => env('CATALOG_SITE_BASE_URL', 'https://komiktap.info'),

    /*
    |--------------------------------------------------------------------------
    | Proxy gambar
    |--------------------------------------------------------------------------
    | coverUrl dan chapter pages ditulis ulang ke endpoint image lokal agar
    | tidak kena blokir hotlink. Hanya host berikut yang diizinkan.
    */
    'image_allowed_hosts' => ['komiktap.info', 'cdn.uqni.net'],
    'image_timeout' => (int) env('CATALOG_IMAGE_TIMEOUT', 15),
    'image_max_bytes' => (int) env('CATALOG_IMAGE_MAX_BYTES', 15728640), // 15 MB

    /*
    |--------------------------------------------------------------------------
    | Rate limit katalog (format "max,decay_menit")
    |--------------------------------------------------------------------------
    */
    'rate_limit' => env('RATE_LIMIT_CATALOG', '60,1'),

];
