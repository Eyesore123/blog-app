<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Redirect old domain to new domain
$oldDomain = 'blog-app-production-16c2.up.railway.app';
$newDomain = 'https://blog.joniputkinen.com';

if (isset($_SERVER['HTTP_HOST']) && strtolower($_SERVER['HTTP_HOST']) === strtolower($oldDomain)) {
    // Preserve the full requested path and query string
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    
    // Optional: show a quick notice before redirect
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Redirecting...</title></head><body>';
    echo '<p>You are being redirected to the new site…</p>';
    echo '</body></html>';
    
    // Permanent redirect (301)
    header('Location: ' . $newDomain . $requestUri, true, 301);
    exit;
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

