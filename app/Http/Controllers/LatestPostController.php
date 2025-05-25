<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;

class LatestPostController extends Controller
{
    public function show()
{
    // Get the latest post that actually has an image
    $latestPostWithImage = Post::whereNotNull('image_url')
                              ->where('image_url', '!=', '')
                              ->latest('created_at')
                              ->first();
    
    // Fallback to latest post regardless of image
    $latestPost = $latestPostWithImage ?? Post::latest('created_at')->first();
    
    if (!$latestPost) {
        return response()->json(['message' => 'No posts found'], 404)
            ->header('Access-Control-Allow-Origin', 'https://jonis-portfolio.netlify.app')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type');
    }

    // Build image URL
    $imageUrl = null;
    if ($latestPost->image_url) {
        $imageUrl = str_starts_with($latestPost->image_url, 'http') 
                   ? $latestPost->image_url 
                   : url($latestPost->image_url);
    } else {
        $imageUrl = url('/fallbackimage.jpg');
    }

    return response()->json([
        'title' => $latestPost->title,
        'url' => route('posts.show', $latestPost),
        'excerpt' => \Illuminate\Support\Str::limit(strip_tags($latestPost->content), 100),
        'publishedAt' => $latestPost->created_at->toDateString(),
        'imageUrl' => $imageUrl,
    ])->header('Access-Control-Allow-Origin', 'https://jonis-portfolio.netlify.app')
      ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
      ->header('Access-Control-Allow-Headers', 'Content-Type');
}

    
    public function options()
    {
        return response('', 200)
            ->header('Access-Control-Allow-Origin', 'https://jonis-portfolio.netlify.app')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type');
    }
}
