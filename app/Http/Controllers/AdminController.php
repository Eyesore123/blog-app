<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class AdminController extends Controller
{
    public function index()
{
    $users = User::select(['id','name','email','is_active'])->get();

    $posts = Post::all();

    return Inertia::render('AdminDashboard', [
        'users' => $users,
        'posts' => $posts,
    ]);
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

        public function toggleUserStatus(User $user)
{
    $user->is_active = !$user->is_active;
    $user->save();

    return back()->with('success', 'User status updated.');
}


    public function deleteUser(User $user)
    {
        $user->delete();

        return redirect()->back()->with('success', 'User deleted.');
    }
}
