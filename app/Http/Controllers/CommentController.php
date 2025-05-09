<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
                    'parent_id' => $comment->parent_id,
                    'deleted' => $comment->deleted ?? false,
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
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = Comment::create([
            'post_id' => $request->post_id,
            'user_id' => Auth::id(),
            'content' => $request->content,
            'parent_id' => $request->parent_id,
            'deleted' => false,
        ]);

        return response()->json([
            '_id' => $comment->id,
            'authorName' => Auth::user()->name ?? 'Anonymous',
            'content' => $comment->content,
            'createdAt' => $comment->created_at->toDateTimeString(),
            'parent_id' => $comment->parent_id,
            'deleted' => false,
        ]);
    }

    public function destroy($id)
    {
        try {
            \Log::info("Attempting to delete comment with ID: {$id}");
            
            $comment = Comment::findOrFail($id);
            \Log::info("Comment found: " . json_encode($comment));
            
            // Check if user is authorized to delete this comment
            if (!Auth::check()) {
                \Log::warning("User not authenticated");
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
            
            if (!Auth::user()->is_admin && Auth::id() !== $comment->user_id) {
                \Log::warning("Unauthorized attempt to delete comment {$id} by user " . Auth::id());
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            
            // Check if this comment has replies or is a reply itself
            $hasReplies = Comment::where('parent_id', $id)->exists();
            $isReply = $comment->parent_id !== null;
            
            \Log::info("Comment {$id} - Has replies: " . ($hasReplies ? 'Yes' : 'No') . ", Is reply: " . ($isReply ? 'Yes' : 'No'));
            
            if ($hasReplies || $isReply) {
                // Soft delete - mark as deleted but keep in database
                $comment->update([
                    'deleted' => true,
                    'content' => '[Message removed by moderator]'
                ]);
                
                \Log::info("Comment {$id} soft-deleted successfully");
                return response()->json([
                    'message' => 'Comment soft-deleted successfully',
                    'softDeleted' => true
                ]);
            } else {
                // Hard delete - remove from database
                $comment->delete();
                
                \Log::info("Comment {$id} hard-deleted successfully");
                return response()->json([
                    'message' => 'Comment deleted successfully',
                    'softDeleted' => false
                ]);
            }
        } catch (\Exception $e) {
            \Log::error("Error deleting comment {$id}: " . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json(['message' => 'Error deleting comment: ' . $e->getMessage()], 500);
        }
    }
}
