<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class RecentActivityController extends Controller
{
    public function index()
    {
        $recentPosts = Post::latest()->take(3)->get()->map(function ($post) {
            return [
                'type' => 'post',
                'title' => $post->title,
                'url' => route('posts.show', $post),
                'createdAt' => $post->created_at->toDateString(),
            ];
        });

        $recentComments = Comment::latest()->take(3)->get()->map(function ($comment) {
            return [
                'type' => 'comment',
                'excerpt' => Str::limit(strip_tags($comment->content), 50),
                'postTitle' => optional($comment->post)->title,
                'postUrl' => $comment->post ? route('posts.show', $comment->post) : null,
                'createdAt' => $comment->created_at->toDateString(),
            ];
        });

        // Merge + sort by createdAt DESC
        $activities = $recentPosts
            ->merge($recentComments)
            ->sortByDesc('createdAt')
            ->values()
            ->take(5);

        return response()->json($activities);
    }
}
