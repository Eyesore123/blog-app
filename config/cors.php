<?php

return [
    'supports_credentials' => true,
    // 'allowed_origins' => ['https://blog-app-production-16c2.up.railway.app/'],
    'allowed_origins' => ['*'],
    'allowed_headers' => ['*'],
    'allowed_methods' => ['*'],  // Or specify 'GET', 'POST', etc.
    'exposed_headers' => [],
    'max_age' => 0,
    'hosts' => [],
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],
];

// For production, restrict access, allowed origins: 
//'allowed_origins' => ['https://jonis-portfolio.netlify.app'],
// 'supports_credentials' => false,
// 'paths' => ['api/*', 'sanctum/csrf-cookie'],

// Default:
// 'allowed_origins' => ['*'],  // Allow all domains or specify your frontend URL here