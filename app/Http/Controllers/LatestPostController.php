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
            return response()->json(['message' => 'No posts found'], 404);
        }

        return response()->json([
            'title' => $latestPost->title,
            'url' => route('posts.show', $latestPost), // adjust route
            'excerpt' => \Illuminate\Support\Str::limit(strip_tags($latestPost->content), 100), // Adjust field name
            'publishedAt' => $latestPost->created_at->toDateString(),
        ]);
    }
}
