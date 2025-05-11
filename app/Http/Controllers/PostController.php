<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    /**
     * Helper method to transform post with image URL
     */
    private function transformPost($post)
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'content' => $post->content,
            'topic' => $post->topic,
            'slug' => $post->slug,
            'created_at' => $post->created_at,
            'updated_at' => $post->updated_at,
            'image_url' => $post->image_path
                ? (strpos($post->image_path, 'uploads/') === 0
                    ? '/' . $post->image_path
                    : '/storage/' . $post->image_path)
                : null,
        ];
    }

    /**
     * Helper method to get authenticated user info (optional)
     */
    private function getUserInfo()
    {
        $user = Auth::user();
        return $user ? [
            'name' => $user->name,
            'is_admin' => $user->is_admin ?? false,
        ] : null;
    }

    /**
     * Render MainPage with optional data
     */
    public function index(Request $request)
    {
        $topicFilter = $request->query('topic');
        $includePosts = $request->query('includePosts', true);
        $includeTopics = $request->query('includeTopics', true);
        $includeUser = $request->query('includeUser', true);

        $props = [];

        if ($includePosts) {
            $query = Post::query();
            if ($topicFilter) {
                $query->where('topic', $topicFilter);
            }

            $posts = $query->latest()->paginate(6);

            $props['posts'] = collect($posts->items())->map(fn($post) => $this->transformPost($post));
            $props['currentPage'] = $posts->currentPage() - 1;
            $props['hasMore'] = $posts->hasMorePages();
            $props['total'] = $posts->total();
            $props['allPosts'] = $posts; // raw pagination data for search or other use
            $props['currentTopic'] = $topicFilter;
        }

        if ($includeTopics) {
            $props['topics'] = Post::distinct()->pluck('topic')->filter()->values();
        }

        if ($includeUser) {
            $props['user'] = $this->getUserInfo();
        }

        return Inertia::render('MainPage', $props);
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

    if (!Auth::check()) {
        return redirect()->route('login')->with('error', 'You must be logged in to create a post.');
    }

    $validated = $request->validate([
        'title' => 'required|string',
        'content' => 'required|string',
        'published' => 'boolean',
        'topic' => 'required|string',
        'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
    ]);

    $imagePath = null;
    if ($request->hasFile('image')) {
        try {
            $file = $request->file('image');
            $imagePath = $file->store('uploads', 'public');  // Store the image using the public disk

            Log::info('Image saved at: ' . $imagePath);
        } catch (\Exception $e) {
            Log::error('Image upload failed: ' . $e->getMessage());
        }
    }

    $post = Post::create([
        'title' => $validated['title'],
        'content' => $validated['content'],
        'topic' => $validated['topic'],
        'published' => $validated['published'] ?? true,
        'image_path' => $imagePath,
        'user_id' => Auth::id() ?? 1,
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

        if (auth()->user() && auth()->user()->is_admin) {
            if ($post->image_path) {
                try {
                    if (strpos($post->image_path, 'uploads/') === 0) {
                        if (file_exists(public_path($post->image_path))) {
                            unlink(public_path($post->image_path));
                            Log::info('Deleted image from uploads: ' . $post->image_path);
                        }
                    } else {
                        Storage::disk('public')->delete($post->image_path);
                        Log::info('Deleted image from storage: ' . $post->image_path);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to delete image: ' . $e->getMessage());
                }
            }

            $post->comments()->delete();
            $post->delete();

            return redirect()->back()->with('success', 'Post deleted successfully.');
        }

        abort(403, 'Unauthorized action.');
    }

    /**
     * Show single post page
     */
    public function show($identifier)
    {
        if (is_numeric($identifier)) {
            $post = Post::with(['comments.user'])->findOrFail($identifier);
        } else {
            $post = Post::with(['comments.user'])->where('slug', $identifier)->firstOrFail();
        }

        $allPosts = Post::all();

        return Inertia::render('PostPage', [
            'post' => $this->transformPost($post),
            'comments' => $post->comments,
            'allPosts' => $allPosts,
            'user' => $this->getUserInfo(),
        ]);
    }

    /**
     * Update a post
     */
    public function update(Request $request, $id)
{
    // Validate the incoming request
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'topic' => 'required|string|max:255',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',  // Ensure it's an image and size limit
    ]);

    $post = Post::findOrFail($id);

    // Update title, content, and topic
    $post->title = $request->title;
    $post->content = $request->content;
    $post->topic = $request->topic;

    // Handle image upload if there's a new one
    if ($request->hasFile('image')) {
        // Delete the old image if it exists
        if ($post->image_path && Storage::disk('public')->exists($post->image_path)) {
            Storage::disk('public')->delete($post->image_path);
        }

        // Store the new image and get its path
        $imagePath = $request->file('image')->store('uploads', 'public');
        $post->image_path = $imagePath;
    }

    // Save the post with the new data
    $post->save();

    return response()->json([
        'message' => 'Post updated successfully!',
        'post' => $post,
    ]);
}





}
