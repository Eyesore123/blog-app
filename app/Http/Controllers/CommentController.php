<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    // GET /api/comments/{post_id}
    public function index($post_id)
    {
        $comments = Comment::where('post_id', $post_id)
            ->with('user')  // include user info
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($comment) {
                return [
                    '_id' => $comment->id,
                    'authorName' => $comment->user->name ?? 'Anonymous',
                    'content' => $comment->content,
                    'createdAt' => $comment->created_at->toDateTimeString(),
                ];
            });

        return response()->json($comments);
    }

    // POST /api/comments
    public function store(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'content' => 'required|string|max:1000',
        ]);

        $comment = Comment::create([
            'post_id' => $request->post_id,
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        return response()->json([
            '_id' => $comment->id,
            'authorName' => Auth::user()->name ?? 'Anonymous',
            'content' => $comment->content,
        ]);
    }

        public function destroy($comment_id)
    {
        $comment = Comment::findOrFail($comment_id);
        $comment->delete();

        return response()->json(['message' => 'Comment deleted']);
    }
}
