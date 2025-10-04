<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Redirect old domain to new domain
$oldDomain = 'blog-app-production-16c2.up.railway.app';
$newDomain = 'https://blog.joniputkinen.com';

if (isset($_SERVER['HTTP_HOST']) && strtolower($_SERVER['HTTP_HOST']) === strtolower($oldDomain)) {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    
    // Send permanent redirect header first
    header('Location: ' . $newDomain . $requestUri, true, 301);
    exit; // stop execution immediately
}

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
