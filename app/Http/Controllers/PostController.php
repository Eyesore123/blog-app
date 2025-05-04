<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Inertia\Inertia;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->paginate(6);

        return Inertia::render('MainPage', [
            'posts' => $posts->items(),
            'currentPage' => $posts->currentPage() - 1,
            'hasMore' => $posts->hasMorePages(),
            'total' => $posts->total(),
            'topics' => [],
            'currentTopic' => null,
            'user' => auth()->user() ? ['name' => auth()->user()->name] : null,
        ]);
    }
}
