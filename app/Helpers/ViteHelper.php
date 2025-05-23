<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Vite;

class ViteHelper
{
    public static function viteAssets(array $entryPoints): HtmlString
    {
        $manifestPath = public_path('build/manifest.json');
        
        if (!File::exists($manifestPath)) {
            // Fallback for development or when manifest doesn't exist
            $devUrl = 'http://localhost:5173';
            $tags = [];
            
            foreach ($entryPoints as $entryPoint) {
                $tags[] = '<script type="module" src="' . $devUrl . '/' . $entryPoint . '"></script>';
            }
            
            return new HtmlString(implode("\n", $tags));
        }
        
        // Use Laravel's built-in Vite helper when manifest exists
        return new HtmlString(Vite::asset($entryPoints));
    }
}
