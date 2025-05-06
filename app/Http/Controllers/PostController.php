<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

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

    public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string',
        'content' => 'required|string',
        'published' => 'boolean',
        'topic' => 'required|string',
    ]);

    Post::create($validated);

    return redirect('/');
}
}
