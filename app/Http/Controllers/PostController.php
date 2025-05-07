<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Inertia\Inertia;
use Inertia\Response;
use Inertia\ResponseFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    /**
     * Display a listing of posts
     */
    public function index(Request $request)
    {
        $topicFilter = $request->query('topic');
        
        // Get all unique topics from existing posts
        $topics = Post::distinct()->pluck('topic')->filter()->values();
        
        // Prepare query (with optional topic filtering)
        $query = Post::query();
        if ($topicFilter) {
            $query->where('topic', $topicFilter);
        }
        
        $posts = $query->latest()->paginate(6);
        
        // Transform posts to include image URLs
        $transformedPosts = collect($posts->items())->map(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'content' => $post->content,
                'topic' => $post->topic,
                'created_at' => $post->created_at,
                // Handle both storage and direct upload paths
                'image_url' => $post->image_path 
                    ? (strpos($post->image_path, 'uploads/') === 0 
                        ? '/' . $post->image_path 
                        : '/storage/' . $post->image_path)
                    : null,
            ];
        });
        
        return Inertia::render('MainPage', [
            'posts' => $transformedPosts,
            'currentPage' => $posts->currentPage() - 1,
            'hasMore' => $posts->hasMorePages(),
            'total' => $posts->total(),
            'topics' => $topics,
            'currentTopic' => $topicFilter,
            'user' => Auth::user() ? [
                'name' => Auth::user()->name,
                'is_admin' => Auth::user()->is_admin ?? false
            ] : null,
        ]);
    }

    /**
     * Store a newly created post
     */
    public function store(Request $request)
    {
        Log::info('Post creation request received', [
            'has_file' => $request->hasFile('image'),
            'all_files' => $request->allFiles(),
            'all_inputs' => $request->all()
        ]);
        
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'You must be logged in to create a post.');
        }
        
        // Validate incoming request
        $validated = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'published' => 'boolean',
            'topic' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
        ]);
        
        // Handle image upload - SIMPLER APPROACH
        $imagePath = null;
        if ($request->hasFile('image')) {
            try {
                // Define $file here before using it
                $file = $request->file('image');
                $filename = time() . '_' . $file->getClientOriginalName();
                
                // Create uploads directory if it doesn't exist
                if (!file_exists(public_path('uploads'))) {
                    mkdir(public_path('uploads'), 0777, true);
                }
                
                // Store directly in public/uploads
                $file->move(public_path('uploads'), $filename);
                $imagePath = 'uploads/' . $filename;
                
                Log::info('Image saved using direct approach at: ' . $imagePath, [
                    'original_name' => $file->getClientOriginalName(),
                    'destination_path' => public_path('uploads'),
                    'full_path' => public_path('uploads') . '/' . $filename,
                    'stored_path' => $imagePath,
                    'public_url' => '/' . $imagePath,
                ]);
            } catch (\Exception $e) {
                Log::error('Image upload failed with direct approach: ' . $e->getMessage());
            }
        } else {
            Log::info('No image uploaded with post');
        }
        
        // Create the post
        $post = Post::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'topic' => $validated['topic'],
            'published' => $validated['published'] ?? true,
            'image_path' => $imagePath,
            'user_id' => Auth::id() ?? 1, // Provide a default if Auth::id() is null
        ]);
        
        Log::info('Post created with ID: ' . $post->id, [
            'post_data' => $post->toArray()
        ]);
        
        return redirect()->back()->with('success', 'Post created successfully.');
    }

    /**
     * Delete a post
     */
    public function destroy($post_id)
    {
        $post = Post::findOrFail($post_id);
        
        // Check if user is admin
        if (auth()->user() && auth()->user()->is_admin) {
            // Delete the post image if it exists
            if ($post->image_path) {
                try {
                    if (strpos($post->image_path, 'uploads/') === 0) {
                        // Direct file upload
                        if (file_exists(public_path($post->image_path))) {
                            unlink(public_path($post->image_path));
                            Log::info('Deleted post image from uploads: ' . $post->image_path);
                        }
                    } else {
                        // Storage approach
                        Storage::disk('public')->delete($post->image_path);
                        Log::info('Deleted post image from storage: ' . $post->image_path);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to delete post image: ' . $e->getMessage());
                }
            }
            
            // Delete associated comments
            $post->comments()->delete();
            
            // Delete the post
            $post->delete();
            
            return redirect()->back()->with('success', 'Post deleted successfully.');
        }
        
        abort(403, 'Unauthorized action.');
    }
}
