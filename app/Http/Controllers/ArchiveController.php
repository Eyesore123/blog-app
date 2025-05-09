<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ArchiveController extends Controller
{
    // Add this method to handle the /archives route
    public function index(Request $request)
    {
        // For SQLite, use strftime instead of YEAR
        if (DB::connection()->getDriverName() === 'sqlite') {
            $years = Post::selectRaw("strftime('%Y', created_at) as year")
                        ->distinct()
                        ->orderBy('year', 'desc')
                        ->pluck('year');
        } else {
            // For MySQL or other databases
            $years = Post::selectRaw('YEAR(created_at) as year')
                        ->distinct()
                        ->orderBy('year', 'desc')
                        ->pluck('year');
        }
                    
        return Inertia::render('ArchiveView', [
            'years' => $years,
            'posts' => ['data' => []],  // Empty posts array
            'allPosts' => [],
            'topics' => [],
            'currentTopic' => null,
            'currentPage' => 0,
            'hasMore' => false,
            'total' => 0,
            'archiveYear' => date('Y'),  // Current year as default
        ]);
    }

    public function getYears()
    {
        try {
            if (DB::connection()->getDriverName() === 'sqlite') {
                // Use strftime for SQLite
                $years = Post::selectRaw("strftime('%Y', created_at) as year")
                    ->distinct()
                    ->orderBy('year', 'desc')
                    ->pluck('year');
            } else {
                // Use YEAR() for MySQL or other databases
                $years = Post::selectRaw("YEAR(created_at) as year")
                    ->distinct()
                    ->orderBy('year', 'desc')
                    ->pluck('year');
            }
    
            \Log::info('Years fetched successfully:', $years->toArray());
    
            return response()->json(['years' => $years]);
        } catch (\Exception $e) {
            \Log::error('Error fetching years:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch years'], 500);
        }
    }

    public function show(Request $request, $year)
{
    $topic = $request->input('topic');
    $page = $request->input('page', 1);
    $perPage = 6;

    // Query to get posts from the specified year
    if (DB::connection()->getDriverName() === 'sqlite') {
        $query = Post::select('id', 'title', 'content', 'topic', 'author', 'created_at', 'slug', 'image_path')
                    ->whereRaw("strftime('%Y', created_at) = ?", [$year]);
    } else {
        $query = Post::select('id', 'title', 'content', 'topic', 'author', 'created_at', 'slug', 'image_path')
                    ->whereYear('created_at', $year);
    }

    // Filter by topic if provided
    if ($topic) {
        $query->where('topic', $topic);
    }

    // Get paginated posts
    $posts = $query->orderBy('created_at', 'desc')
                  ->paginate($perPage);

    // Transform posts to include `image_url`
    $posts->getCollection()->transform(function ($post) {
        $post->image_url = $post->image_path 
            ? (strpos($post->image_path, 'uploads/') === 0 
                ? '/' . $post->image_path 
                : '/storage/' . $post->image_path)
            : null;
        return $post;
    });

    // Get all posts for the specified year
    if (DB::connection()->getDriverName() === 'sqlite') {
        $allPosts = Post::select('id', 'title', 'content', 'topic', 'author', 'created_at', 'slug', 'image_path')
                       ->whereRaw("strftime('%Y', created_at) = ?", [$year])
                       ->orderBy('created_at', 'desc')
                       ->get();
    } else {
        $allPosts = Post::select('id', 'title', 'content', 'topic', 'author', 'created_at', 'slug', 'image_path')
                       ->whereYear('created_at', $year)
                       ->orderBy('created_at', 'desc')
                       ->get();
    }

    // Transform `allPosts` to include `image_url`
    $allPosts->transform(function ($post) {
        $post->image_url = $post->image_path 
            ? (strpos($post->image_path, 'uploads/') === 0 
                ? '/' . $post->image_path 
                : '/storage/' . $post->image_path)
            : null;
        return $post;
    });

    // Get all unique topics for the specified year
    $topics = DB::connection()->getDriverName() === 'sqlite'
        ? Post::whereRaw("strftime('%Y', created_at) = ?", [$year])
              ->distinct('topic')
              ->pluck('topic')
              ->toArray()
        : Post::whereYear('created_at', $year)
              ->distinct('topic')
              ->pluck('topic')
              ->toArray();

    return Inertia::render('ArchiveView', [
        'posts' => $posts,
        'allPosts' => $allPosts,
        'topics' => $topics,
        'currentTopic' => $topic,
        'currentPage' => $page - 1, // Adjust for zero-based indexing in frontend
        'hasMore' => $posts->hasMorePages(),
        'total' => $posts->total(),
        'archiveYear' => (int)$year,
    ]);
}
}
