<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\RateLimitService;
use App\Models\User;
use App\Notifications\NewCommentNotification;

class CommentController extends Controller
{
    protected $rateLimiter;

    public function __construct(RateLimitService $rateLimiter)
    {
        $this->rateLimiter = $rateLimiter;
    }

    public function index($post_id)
    {
        $comments = Comment::where('post_id', $post_id)
            ->with('user')  // include user info
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($comment) {
                return [
                    '_id' => $comment->id,
                    'authorName' => $comment->user->name ?? $comment->guest_name ?? 'Anonymous',
                    'content' => $comment->content,
                    'createdAt' => $comment->created_at->toDateTimeString(),
                    'parent_id' => $comment->parent_id,
                    'user_id' => $comment->user_id,
                    'edited' => $comment->edited,
                    'deleted' => $comment->deleted ?? false,
                ];
            });

        return response()->json($comments);
    }

    public function store(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        // Skip rate limiting for admin users
        if (!(Auth::check() && Auth::user()->is_admin)) {
            $remaining = $this->rateLimiter->getRemainingComments();

            if ($remaining <= 0) {
                return response()->json([
                    'message' => 'You have reached the maximum of 10 comments today. Please try again tomorrow.',
                ], 429);
            }

            // Increment count after passing check
            $this->rateLimiter->incrementCommentCount();
        }

        $comment = Comment::create([
            'post_id' => $request->post_id,
            'user_id' => Auth::id(),
            'guest_name' => Auth::check() ? null : $request->cookie('anonId'),
            'content' => $request->content,
            'parent_id' => $request->parent_id,
            'deleted' => false,
            'edited' => false,
        ]);

        // Notify admin for every comment
        try {
        $admin = User::where('is_admin', true)->first();
        if ($admin) {
            Log::info('Admin for notification:', ['admin' => $admin]);
            $admin->notify(new NewCommentNotification($comment));
        }
        } catch (\Exception $e) {
            Log::error('Failed to send comment notification: ' . $e->getMessage());
        }

        // Notify parent comment author if it is a reply and they want notifications
        
        if ($comment->parent_id) {
            $parentComment = Comment::find($comment->parent_id);
            if ($parentComment && $parentComment->user_id) {
                $parentUser = User::find($parentComment->user_id);
                if ($parentUser && $parentUser->notify_comments) {
                    try {
                        $parentUser->notify(new NewCommentNotification($comment));
                    } catch (\Exception $e) {
                        Log::error('Failed to send reply notification to user: ' . $e->getMessage());
                    }
                }
            }
        }

        return response()->json([
            '_id' => $comment->id,
            'authorName' => Auth::check() ? Auth::user()->name : $request->cookie('anonId'),
            'content' => $comment->content,
            'createdAt' => $comment->created_at->toDateTimeString(),
            'parent_id' => $comment->parent_id,
            'deleted' => false,
            'edited' => false,
        ]);
    }

    public function destroy($id)
    {
        try {
            Log::info("Attempting to delete comment with ID: {$id}");

            $comment = Comment::findOrFail($id);
            Log::info("Comment found: " . json_encode($comment));

            // Check if user is authorized to delete this comment
            if (!Auth::check()) {
                Log::warning("User not authenticated");
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            if (!Auth::user()->is_admin && Auth::id() !== $comment->user_id) {
                Log::warning("Unauthorized attempt to delete comment {$id} by user " . Auth::id());
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Check if this comment has replies or is a reply itself
            $hasReplies = Comment::where('parent_id', $id)->exists();
            $isReply = $comment->parent_id !== null;

            Log::info("Comment {$id} - Has replies: " . ($hasReplies ? 'Yes' : 'No') . ", Is reply: " . ($isReply ? 'Yes' : 'No'));

            // Check if user wants to remove comments when deleting their account
            $removeComments = request()->has('remove_comments') && request()->input('remove_comments') === 'yes';

            if ($removeComments) {
                // Update comment content to indicate it was removed by user
                $comment->update([
                    'content' => 'Deleted by user',
                ]);
                Log::info("Remove comments parameter: " . request()->input('remove_comments'));
                Log::info("Comment {$id} updated successfully");
            }

            if ($hasReplies || $isReply) {
                // Soft delete - mark as deleted but keep in database
                $comment->update([
                    'deleted' => true,
                    'content' => '[Message removed by moderator]',
                ]);

                Log::info("Comment {$id} soft-deleted successfully");
                return response()->json([
                    'message' => 'Comment soft-deleted successfully',
                    'softDeleted' => true,
                ]);
            } else {
                // Hard delete - remove from database
                $comment->delete();

                Log::info("Comment {$id} hard-deleted successfully");
                return response()->json([
                    'message' => 'Comment deleted successfully',
                    'softDeleted' => false,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error deleting comment {$id}: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['message' => 'Error deleting comment: ' . $e->getMessage()], 500);
        }
    }

    public function getRemaining(Request $request)
    {
        // Admins have unlimited comments
        if (Auth::check() && Auth::user()->is_admin) {
            return response()->json([
                'remaining' => 'unlimited',
                'is_admin' => true,
            ]);
        }

        $remaining = $this->rateLimiter->getRemainingComments();

        return response()->json([
            'remaining' => $remaining,
            'is_admin' => false,
        ]);
    }

    /**
 * Update a comment (for comment owners)
 */
public function update(Request $request, $id)
{
    $request->validate([
        'content' => 'required|string|max:1000',
    ]);

    $user = Auth::user();
    $comment = Comment::find($id);
    
    if (!$comment) {
        return response()->json(['message' => 'Comment not found'], 404);
    }
    
    // Check if the user is the comment owner
    if ($comment->user_id !== $user->id) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    
    $comment->content = $request->content;
    $comment->edited = true;
    $comment->save();
    
    return response()->json($comment);
}

/**
 * Delete a comment (for comment owners)
 */
public function userDelete($id)
{
    $user = Auth::user();
    $comment = Comment::find($id);
    
    if (!$comment) {
        return response()->json(['message' => 'Comment not found'], 404);
    }
    
    // Check if the user is the comment owner
    if ($comment->user_id !== $user->id) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    
    // Check if the comment has replies
    $hasReplies = Comment::where('parent_id', $id)->exists();
    
    if ($hasReplies) {
        return response()->json(['message' => 'Cannot delete a comment with replies'], 400);
    }
    
    $comment->delete();
    
    return response()->json(['message' => 'Comment deleted successfully']);
}

    public function userComments(User $user)
    {
        try {
            // Fetch comments with related post data
            $comments = $user->comments()
                ->with('post:id,slug,title') // Eager load post data
                ->latest()
                ->get()
                ->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'content' => $comment->content,
                        'post_title' => $comment->post->title ?? 'Unknown Post',
                        'post_slug' => $comment->post->slug ?? '#',
                        'created_at' => $comment->created_at,
                    ];
                });

            // Log the number of comments fetched
            Log::info("Fetched " . $comments->count() . " comments for user ID: {$user->id}");

            return response()->json($comments);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error("Error fetching comments for user ID: {$user->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to fetch comments. Please try again later.',
            ], 500);
        }
    }

    public function removeCommentsForUser($userId)
    {
        // Remove all comments for the given user
        Comment::where('user_id', $userId)->delete();
    }


    }
