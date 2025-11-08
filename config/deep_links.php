<?php

return [
    /**
     * Fallback URLs for when app is not installed
     * Used when deep link cannot open the app
     */
    'fallback_url' => env('DEEP_LINK_FALLBACK_URL', 'https://play.google.com/store/apps/details?id=com.hakimyar.hekmat_sara'),

    'ios_fallback_url' => env('DEEP_LINK_IOS_FALLBACK_URL', 'https://apps.apple.com/app/hekmat-sara'),

    /**
     * Deep link token TTL (in minutes)
     * Tokens expire after this duration and cannot be reused
     */
    'token_ttl_minutes' => env('DEEP_LINK_TOKEN_TTL_MINUTES', 5),
];
