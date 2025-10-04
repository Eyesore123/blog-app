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
                ->header('Access-Control-Allow-Origin', 'https://joniputkinen.com')
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
        ])->header('Access-Control-Allow-Origin', 'https://joniputkinen.com')
          ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
          ->header('Access-Control-Allow-Headers', 'Content-Type');
    }

    private function getImageUrl($post)
    {
        // Check the correct column name: image_path
        if (isset($post->image_path) && !empty($post->image_path)) {
            $imagePath = $post->image_path;
            
            // If it's already a full URL, return as-is
            if (str_starts_with($imagePath, 'http')) {
                return $imagePath;
            }
            
            // If it's a relative path, fix the path structure
            if (str_starts_with($imagePath, 'uploads/')) {
                // Convert "uploads/filename.jpg" to "/storage/uploads/filename.jpg"
                return url('/storage/' . $imagePath);
            } elseif (str_starts_with($imagePath, '/uploads/')) {
                // Convert "/uploads/filename.jpg" to "/storage/uploads/filename.jpg"
                return url('/storage' . $imagePath);
            } else {
                // Assume it's just the filename or already has correct path
                return url($imagePath);
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
            ->header('Access-Control-Allow-Origin', 'https://joniputkinen.com')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type');
    }
}
