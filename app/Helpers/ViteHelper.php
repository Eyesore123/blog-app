<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Log;

class ViteHelper
{
    public static function viteAssets(array $entryPoints): HtmlString
    {
        $manifestPath = public_path('build/manifest.json');
        $altManifestPath = public_path('build/.vite/manifest.json');
        
        // Check if the manifest exists in the default location
        if (File::exists($manifestPath)) {
            return new HtmlString(Vite::useBuildDirectory('build')->withEntryPoints($entryPoints)->toHtml());
        }
        
        // Check if the manifest exists in the .vite directory
        if (File::exists($altManifestPath)) {
            // Copy the manifest to the expected location
            File::copy($altManifestPath, $manifestPath);
            return new HtmlString(Vite::useBuildDirectory('build')->withEntryPoints($entryPoints)->toHtml());
        }
        
        // Log the error if no manifest is found
        Log::error('Vite manifest not found at: ' . $manifestPath . ' or ' . $altManifestPath);
        
        // Return a fallback
        return new HtmlString('
            <link rel="stylesheet" href="/build/assets/app-DWrjc4j3.css">
            <script type="module" src="/build/assets/app-_lpql_RX.js"></script>
        ');
    }
}
