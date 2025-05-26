<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Models\Tag;


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

    // Check if the post has any tags
    if ($post->tags->count() > 0) {
        // Check if the tag already exists
        $tagName = $post->tags->first()->name; // Get the first tag name
        $tag = Tag::firstOrCreate(['name' => $tagName]);
        $post->tags()->syncWithoutDetaching([$tag->id]);
        // IF doesn't work, try this:
        // $post->tags()->toggle($tag->id);
    }

    return Inertia::render('EditPostPage', [
        'post' => [
            'id' => $post->id,
            'title' => $post->title,
            'content' => $post->content,
            'topic' => $post->topic,
            'image_url' => $imageUrl,
            'published' => $post->published,
            'tags' => $post->tags, // Pass the entire tags collection
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
