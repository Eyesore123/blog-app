<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // Method to store a new comment
    public function store(Request $request)
    {
        // Validate incoming data
        $request->validate([
            'content' => 'required|string|max:500',
            'post_id' => 'required|exists:posts,id',
        ]);
        
        // Check if user is authenticated
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Create and save a new comment
        $comment = new Comment();
        $comment->content = $request->content;
        $comment->post_id = $request->post_id;
        $comment->user_id = auth()->id();
        $comment->save();
        
        // Return the created comment in the expected format
        return response()->json([
            '_id' => $comment->id,
            'authorName' => auth()->user()->name,
            'content' => $comment->content,
        ], 201);
    }

    // Method to fetch comments for a specific post
    public function index($postId)
    {
        $comments = Comment::where('post_id', $postId)
            ->with('user')
            ->get()
            ->map(function ($comment) {
                return [
                    '_id' => $comment->id,
                    'authorName' => $comment->user->name,
                    'content' => $comment->content,
                ];
            });
        
        return response()->json($comments);
    }

    // Method to fetch a single comment by ID (not used in the frontend, but included here)
    public function show($id)
    {
        $comment = Comment::findOrFail($id);
        return response()->json($comment);
    }
}
