<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Post;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class AdminController extends Controller
{
    public function index()
    {
        return Inertia::render('AdminDashboard');
    }

    public function edit($id)
    {
        $post = Post::findOrFail($id);

        $imageUrl = $post->image_path
            ? (strpos($post->image_path, 'uploads/') === 0
                ? '/' . $post->image_path
                : '/storage/' . $post->image_path)
            : null;

        Log::info('Editing post', ['post_id' => $post->id]);

        return Inertia::render('EditPostPage', [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'content' => $post->content,
                'topic' => $post->topic,
                'image_url' => $imageUrl,
                'published' => $post->published,
            ],
        ]);
    }
}
