<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Fansku Public API Configuration
    |--------------------------------------------------------------------------
    |
    | Primary payment gateway for all transactions (licenses + donations)
    | via QRIS. Set FANSKU_IS_ENABLED=true in .env to activate.
    | Get API key from Fansku creator dashboard.
    |
    */

    'is_enabled' => env('FANSKU_IS_ENABLED', false),

    'api_key'        => env('FANSKU_API_KEY', ''),
    'webhook_secret' => env('FANSKU_WEBHOOK_SECRET', ''),

    'base_url' => env('FANSKU_BASE_URL', 'https://api.fansku.id/api/v1/public-api'),

    // QRIS-only scope: other slugs are rejected at service level.
    'payment_method' => 'qris',

    // Short cache (seconds) for payment-methods lookup to avoid
    // adding API latency to every checkout request.
    'methods_cache_ttl' => env('FANSKU_METHODS_CACHE_TTL', 600),

];
