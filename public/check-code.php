<?php

$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

echo "<h1>Code Check</h1>";

// Define the base path
$basePath = __DIR__ . '/..';

// Function to search for a string in files
function searchInFiles($directory, $searchString, $extensions = ['php', 'blade.php']) {
    $results = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $extension = pathinfo($file->getPathname(), PATHINFO_EXTENSION);
            if (in_array($extension, $extensions)) {
                $content = file_get_contents($file->getPathname());
                if (strpos($content, $searchString) !== false) {
                    $relativePath = str_replace($directory . '/', '', $file->getPathname());
                    $lineNumber = 1;
                    $lines = explode("\n", $content);
                    $matchingLines = [];
                    
                    foreach ($lines as $line) {
                        if (strpos($line, $searchString) !== false) {
                            $matchingLines[$lineNumber] = trim($line);
                        }
                        $lineNumber++;
                    }
                    
                    $results[$relativePath] = $matchingLines;
                }
            }
        }
    }
    
    return $results;
}

echo "<h2>Files Accessing 'topic' Column</h2>";
echo "<pre>";
$topicResults = searchInFiles($basePath, 'topic');

if (empty($topicResults)) {
    echo "No files found that reference 'topic'.\n";
} else {
    echo "Files referencing 'topic':\n";
    foreach ($topicResults as $file => $lines) {
        echo "- $file\n";
        foreach ($lines as $lineNumber => $line) {
            echo "  Line $lineNumber: $line\n";
        }
        echo "\n";
    }
}
echo "</pre>";

echo "<h2>Files with 'select distinct' Queries</h2>";
echo "<pre>";
$distinctResults = searchInFiles($basePath, 'select distinct');

if (empty($distinctResults)) {
    echo "No files found with 'select distinct' queries.\n";
} else {
    echo "Files with 'select distinct' queries:\n";
    foreach ($distinctResults as $file => $lines) {
        echo "- $file\n";
        foreach ($lines as $lineNumber => $line) {
            echo "  Line $lineNumber: $line\n";
        }
        echo "\n";
    }
}
echo "</pre>";

echo "<p>Done. <a href='/'>Go to homepage</a></p>";
