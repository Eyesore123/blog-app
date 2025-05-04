<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Inertia\Inertia;

class PostController extends Controller
{
    public function index()
    {
        // Fetch all posts
        $posts = Post::latest()->get();

        // Return to Inertia + React component (MainPage)
        return Inertia::render('MainPage', [
            'posts' => $posts,
        ]);
    }
}
