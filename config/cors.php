<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',
        'http://localhost:3001',
        'https://localhost:3000',
        'https://localhost:3001',
        // Savvy Post Marketing
        'https://savvypostmarketing.com',
        'https://www.savvypostmarketing.com',
        // Savvy Tech Innovation
        'https://savvytechinnovation.com',
        'https://www.savvytechinnovation.com',
        // Environment URLs
        env('FRONTEND_URL', 'http://localhost:3000'),
        env('FRONTEND_URL_TECH', 'http://localhost:3001'),
    ],

    'allowed_origins_patterns' => [
        '#^https?://.*\.savvypostmarketing\.com$#',
        '#^https?://.*\.savvytechinnovation\.com$#',
        '#^https?://.*\.vercel\.app$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
