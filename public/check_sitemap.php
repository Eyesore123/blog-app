<?php
// Security check
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

// point to public/sitemap.xml
$sitemapFile = __DIR__ . '/../public/sitemap.xml';

if (!file_exists($sitemapFile)) {
    echo "<h1>❌ Sitemap not found.</h1>";
    echo "<p>Looked for: {$sitemapFile}</p>";
    exit;
}

$sitemap = file_get_contents($sitemapFile);
$lastMod = date("Y-m-d H:i:s", filemtime($sitemapFile));

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Sitemap Status</title>
    <link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'>
</head>
<body class='bg-gray-100 p-8'>
<div class='max-w-4xl mx-auto bg-white p-6 rounded-lg shadow'>
    <h1 class='text-2xl font-bold mb-4'>✅ Sitemap Status</h1>
    <p class='mb-2'><strong>Path:</strong> {$sitemapFile}</p>
    <p class='mb-4'><strong>Last Modified:</strong> {$lastMod}</p>
    <h2 class='text-lg font-semibold mb-2'>📄 Contents</h2>
    <pre class='bg-gray-100 p-4 rounded text-xs overflow-x-auto max-h-96 overflow-y-auto'>"
        . htmlspecialchars($sitemap) .
    "</pre>
</div>
</body>
</html>";
