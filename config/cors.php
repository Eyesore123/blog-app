<?php

return [
    'supports_credentials' => true,
    'allowed_origins' => ['https://jonis-blog.netlify.app'],
    'allowed_headers' => ['*'],
    'allowed_methods' => ['*'],  // Or specify 'GET', 'POST', etc.
    'exposed_headers' => [],
    'max_age' => 0,
    'hosts' => [],
];

// For production, restrict access, allowed origins: 
//'allowed_origins' => ['https://jonis-portfolio.netlify.app'],
// 'supports_credentials' => false,
// 'paths' => ['api/*', 'sanctum/csrf-cookie'],

// Default:
// 'allowed_origins' => ['*'],  // Allow all domains or specify your frontend URL here