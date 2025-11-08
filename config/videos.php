<?php

return [
    /**
     * Allowed video domains for URL validation
     * Only URLs from these domains are accepted
     */
    'allowed_domains' => [
        'youtube.com',
        'youtu.be',
        'vimeo.com',
        'cdn.example.com',
        'video.example.com',
        'file-examples.com', // Test video domain
        'devstreaming-cdn.apple.com', // Apple HLS test streams
    ],

    /**
     * Encryption configuration
     * Uses Laravel's built-in Crypt which uses APP_KEY
     */
    'encryption' => [
        'cipher' => 'AES-256-CBC',
        // Uses APP_KEY from .env (set via php artisan key:generate)
    ],

    /**
     * Stream token lifetime in minutes
     * Tokens expire after this duration and cannot be reused
     */
    'token_ttl_minutes' => 5,

    /**
     * URL validation settings
     */
    'validation' => [
        // Timeout for HEAD request when validating URL accessibility (3 seconds prevents blocking)
        // If external service is slow, validation fails gracefully without blocking instructor
        'head_request_timeout' => 3,
        // Require HTTPS for all video URLs (security)
        'require_https' => true,
    ],
];
