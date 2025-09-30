<?php
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

$sitemapFile = __DIR__ . '/../sitemap.xml';

if (!file_exists($sitemapFile)) {
    echo "❌ Sitemap not found.";
    exit;
}

$sitemap = file_get_contents($sitemapFile);
$lastMod = date("Y-m-d H:i:s", filemtime($sitemapFile));

echo "<h1>✅ Sitemap Status</h1>";
echo "<p>Path: {$sitemapFile}</p>";
echo "<p>Last Modified: {$lastMod}</p>";
echo "<pre>" . htmlspecialchars($sitemap) . "</pre>";
