<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;

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

        return response()->json([
            'title' => $latestPost->title,
            'url' => route('posts.show', $latestPost),
            'excerpt' => \Illuminate\Support\Str::limit(strip_tags($latestPost->content), 100),
            'publishedAt' => $latestPost->created_at->toDateString(),
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
