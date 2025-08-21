<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Services\SeoService;
use Illuminate\Support\Str;
use App\Mail\NewPostNotification;
use Illuminate\Support\Facades\Mail;

class PostController extends Controller
{
    /**
     * Normalize a Post model into the shape your React components expect.
     */
    private function transformPost($post)
    {
        return [
            'id'         => $post->id,
            'title'      => $post->title,
            'content'    => $post->content,
            'topic'      => $post->topic,
            'slug'       => $post->slug,
            'created_at' => $post->created_at,
            'updated_at' => $post->updated_at,
            'image_url'  => $post->image_path ? str_replace('\\', '', Storage::url($post->image_path)) : null,
            'tags'       => $post->tags->map(fn($t) => ['id' => $t->id, 'name' => $t->name]),
        ];
    }

    /**
     * Grab the current authenticated user info for the front end.
     */
    private function getUserInfo()
    {
        $user = Auth::user();
        return $user ? [
            'name'     => $user->name,
            'is_admin' => $user->is_admin ?? false,
        ] : null;
    }

    /**
     * List posts, optionally filtered by topic.
     */
    public function index(Request $request)
{
    $topicFilter = $request->query('topic');
    $props = [];

    // Pagination query for main posts
    $query = Post::with('tags');
    if ($topicFilter) {
        $query->where('topic', $topicFilter);
    }
    $posts = $query->latest()->paginate(6);

    // Query ALL posts for filter, but only needed fields
    $allPostsForFilter = Post::select('id', 'title', 'topic', 'created_at')->get();

    $props['posts'] = collect($posts->items())->map(fn($p) => $this->transformPost($p));
    $props['currentPage'] = $posts->currentPage() - 1;
    $props['hasMore'] = $posts->hasMorePages();
    $props['total'] = $posts->total();
    $props['allPosts'] = $posts; // paginated posts for main listing
    $props['allPostsForFilter'] = $allPostsForFilter; // all posts for year filter
    $props['currentTopic'] = $topicFilter;

    $props['topics'] = Post::distinct()->pluck('topic')->filter()->values();
    $props['user'] = $this->getUserInfo();

    return Inertia::render('MainPage', $props);
}


    /**
     * Show form to create a new post — omitted here if you handle that on the front end.
     */

    /**
     * Store a newly created post, resolving tag names to IDs.
     */
    public function store(Request $request)
    {
        Log::info('Post creation request received', [
            'has_file'  => $request->hasFile('image'),
            'all_files' => $request->allFiles(),
            'all_inputs'=> $request->all(),
        ]);

        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'You must be logged in to create a post.');
        }

        $validated = $request->validate([
            'title'    => 'required|string',
            'content'  => 'required|string',
            'published'=> 'boolean',
            'topic'    => 'required|string',
            'image'    => 'nullable|image|max:10000',
            'tags'     => 'nullable|array',
            'tags.*'   => 'string|max:255',
        ]);

        $imagePath = null;
        DB::beginTransaction();

        try {
            if ($request->hasFile('image')) {
                $file      = $request->file('image');
                $imagePath = $file->store('uploads', 'public');
                Log::info("Image saved at: {$imagePath}");
            }

            $baseSlug = Str::slug($validated['title']);
            $slug     = $baseSlug;
            $counter  = 1;
            while (Post::where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            $post = Post::create([
                'title'      => $validated['title'],
                'content'    => $validated['content'],
                'topic'      => $validated['topic'],
                'published'  => $validated['published'] ?? true,
                'image_path' => $imagePath,
                'user_id'    => Auth::id(),
                'slug'       => $slug,
            ]);

            if (!empty($validated['tags'])) {
                $tagIds = collect($validated['tags'])
                    ->map(fn($name) => Tag::firstOrCreate(['name' => $name])->id)
                    ->all();
                $post->tags()->sync($tagIds);
            }

            DB::commit();
            Log::info("Post created with ID: {$post->id}", ['post_data' => $post->toArray()]);

            $subscribers = User::where('is_subscribed', 1)->get();

            foreach ($subscribers as $subscriber) {
                Mail::to($subscriber->email)->send(new NewPostNotification($post, $subscriber->email));
            }

            return redirect()->back()->with('success', 'Post created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
                Log::warning("Rolled back image upload: {$imagePath}");
            }
            Log::error("Post creation failed: {$e->getMessage()}");
            return redirect()->back()->with('error', 'Failed to create post.');
        }
    }

    /**
     * Display a single post (by ID or slug).
     */
    public function show($identifier)
    {
        $postQuery = Post::with(['comments.user', 'tags']);
        $post = is_numeric($identifier)
            ? $postQuery->findOrFail($identifier)
            : $postQuery->where('slug', $identifier)->firstOrFail();

        $seo      = SeoService::forPost($post);
        $allPosts = Post::all();

        return Inertia::render('PostPage', [
            'post'     => $this->transformPost($post),
            'comments' => $post->comments,
            'allPosts' => $allPosts,
            'user'     => $this->getUserInfo(),
            'seo'      => $seo,
        ]);
    }

    /**
     * Update an existing post, syncing tags by name.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
            'topic'   => 'required|string|max:255',
            'image'   => 'nullable|image|max:10000',
            'tags'    => 'nullable|array',
            'tags.*'  => 'string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $post = Post::findOrFail($id);
            $post->title   = $validated['title'];
            $post->content = $validated['content'];
            $post->topic   = $validated['topic'];

            if (isset($validated['tags'])) {
                $tagIds = collect($validated['tags'])
                    ->map(fn($name) => Tag::firstOrCreate(['name' => $name])->id)
                    ->all();
                $post->tags()->sync($tagIds);
            }

            if ($request->hasFile('image')) {
                if ($post->image_path && file_exists(public_path("storage/{$post->image_path}"))) {
                    unlink(public_path("storage/{$post->image_path}"));
                }
                $post->image_path = $request->file('image')->store('uploads', 'public');
            }

            $post->save();
            DB::commit();

            return redirect()->back()->with('success', 'Post updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Post update failed: {$e->getMessage()}");
            return response()->json(['error' => 'Update failed'], 500);
        }
    }

    /**
     * Delete a post (admin only).
     */
    public function destroy(Post $post)
{
    if (!Auth::user()?->is_admin) {
        abort(403, 'Unauthorized action.');
    }

    try {
        if ($post->image_path) {
            Storage::disk('public')->delete($post->image_path);
        }
        $post->comments()->delete();
        $post->delete();
        return redirect('/')->with('success', 'Post deleted successfully.');
    } catch (\Throwable $e) {
        Log::error("Failed to delete post: {$e->getMessage()}");
        return redirect()->back()->with('error', 'Failed to delete post.');
    }
}


    /**
     * Show posts filtered by a tag name.
     */
    public function filterByTag(Tag $tag)
    {
        $posts = $tag->posts()
            ->where('published', true)
            ->with('tags')
            ->latest()
            ->get()
            ->map(fn($p) => $this->transformPost($p));

        return Inertia::render('posts/Index', [
            'posts'     => $posts,
            'activeTag' => $tag->name,
        ]);
    }

    // Store the translation of a post

    public function storeTranslation(Request $request, Post $post)
    {
        $data = $request->validate([
            'lang' => 'required|string',
            'title' => 'nullable|string',
            'content' => 'required|string',
        ]);

        $translations = $post->translations ?? [];
        $translations[$data['lang']] = [
            'title' => $data['title'] ?? '',
            'content' => $data['content'],
        ];

        $post->translations = $translations;
        $post->save();

        return response()->json(['success' => true]);
    }

    public function suggested($slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();
        $tagIds = $post->tags->pluck('id');

        $suggested = Post::where('id', '!=', $post->id)
            ->whereHas('tags', function ($query) use ($tagIds) {
                $query->whereIn('tags.id', $tagIds);
            })
            ->inRandomOrder()
            ->limit(12)
            ->get(['id', 'title', 'slug', 'image_path']);
        return response()->json($suggested);
    }
}