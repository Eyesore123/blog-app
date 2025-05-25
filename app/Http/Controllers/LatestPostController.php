<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Support\Facades\File;

class LatestPostController extends Controller
{
    public function show()
    {
        $latestPost = Post::latest('created_at')->first();
        
        if (!$latestPost) {
            return response()->json(['message' => 'No posts found'], 404)
                ->header('Access-Control-Allow-Origin', 'https://jonis-portfolio.netlify.app')
                ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type');
        }

        // Get image URL using the correct column name
        $imageUrl = $this->getImageUrl($latestPost);

        return response()->json([
            'title' => $latestPost->title,
            'url' => route('posts.show', $latestPost),
            'excerpt' => \Illuminate\Support\Str::limit(strip_tags($latestPost->content), 100),
            'publishedAt' => $latestPost->created_at->toDateString(),
            'imageUrl' => $imageUrl,
            'debug_columns' => array_keys($latestPost->getAttributes()), // Debug: see all columns
        ])->header('Access-Control-Allow-Origin', 'https://jonis-portfolio.netlify.app')
          ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
          ->header('Access-Control-Allow-Headers', 'Content-Type');
    }

    private function getImageUrl($post)
    {
        // Try different possible column names
        $imageColumns = ['image_url', 'image', 'image_path', 'featured_image', 'thumbnail'];
        
        foreach ($imageColumns as $column) {
            if (isset($post->$column) && !empty($post->$column)) {
                $imageValue = $post->$column;
                
                if (str_starts_with($imageValue, 'http')) {
                    return $imageValue;
                } else {
                    return url($imageValue);
                }
            }
        }

        // Fallback: get latest uploaded file
        return $this->getLatestUploadedFile();
    }

    private function getLatestUploadedFile()
    {
        $uploadsPath = storage_path('app/public/uploads');
        
        if (File::exists($uploadsPath)) {
            $files = File::files($uploadsPath);
            
            if (!empty($files)) {
                // Sort by modification time (newest first)
                usort($files, function($a, $b) {
                    return $b->getMTime() - $a->getMTime();
                });
                
                $latestFile = $files[0];
                $filename = $latestFile->getFilename();
                
                return url("/storage/uploads/{$filename}");
            }
        }

        // Final fallback
        return url('/fallbackimage.jpg');
    }

    public function options()
    {
        return response('', 200)
            ->header('Access-Control-Allow-Origin', 'https://jonis-portfolio.netlify.app')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type');
    }
}
