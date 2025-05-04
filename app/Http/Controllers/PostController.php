<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

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
            'user' => Auth::user() ? ['name' => Auth::user()->name] : null,
        ]);
    }
}
