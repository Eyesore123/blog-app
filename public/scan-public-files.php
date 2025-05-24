<?php
// Simple security check
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    http_response_code(404);
    echo "Not Found";
    exit;
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Public Folder Scanner</title>
    <link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'>
</head>
<body class='bg-gray-100 p-8'>
    <h1 class='text-2xl font-bold mb-4'>Public Folder File Scanner</h1>";

$publicDir = __DIR__;
$allFiles = scandir($publicDir);

echo "<h2 class='text-xl font-semibold mb-4'>All Files in Public Directory:</h2>";
echo "<div class='bg-white p-4 rounded shadow mb-6'>";
echo "<table class='w-full'>";
echo "<tr class='border-b'><th class='text-left p-2'>Filename</th><th class='text-left p-2'>Type</th><th class='text-left p-2'>Size</th><th class='text-left p-2'>Modified</th></tr>";

$phpFiles = [];
$otherFiles = [];

foreach ($allFiles as $file) {
    if ($file === '.' || $file === '..') continue;
    
    $filepath = $publicDir . '/' . $file;
    if (is_file($filepath)) {
        $filesize = filesize($filepath);
        $modified = date('Y-m-d H:i:s', filemtime($filepath));
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        
        if ($extension === 'php') {
            $phpFiles[] = $file;
            $rowClass = 'bg-blue-50';
        } else {
            $otherFiles[] = $file;
            $rowClass = 'bg-gray-50';
        }
        
        echo "<tr class='$rowClass'>";
        echo "<td class='p-2'>$file</td>";
        echo "<td class='p-2'>$extension</td>";
        echo "<td class='p-2'>" . number_format($filesize) . " bytes</td>";
        echo "<td class='p-2'>$modified</td>";
        echo "</tr>";
    }
}

echo "</table>";
echo "</div>";

echo "<h2 class='text-xl font-semibold mb-4'>PHP Files Found (" . count($phpFiles) . "):</h2>";
echo "<div class='bg-white p-4 rounded shadow mb-6'>";
echo "<ul class='list-disc list-inside'>";
foreach ($phpFiles as $file) {
    echo "<li class='mb-1'>$file</li>";
}
echo "</ul>";
echo "</div>";

echo "<h2 class='text-xl font-semibold mb-4'>Copy this list for the admin dashboard:</h2>";
echo "<div class='bg-gray-800 text-green-400 p-4 rounded font-mono text-sm'>";
echo "<pre>";
foreach ($phpFiles as $file) {
    echo "'$file' => 'Description for $file',\n";
}
echo "</pre>";
echo "</div>";

echo "</body></html>";
?>
