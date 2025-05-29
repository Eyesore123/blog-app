<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ArchiveController extends Controller
{
    public function index(Request $request)
    {
        // Get distinct years from posts depending on DB driver
        $years = DB::connection()->getDriverName() === 'sqlite'
            ? Post::selectRaw("strftime('%Y', created_at) as year")->distinct()->orderBy('year', 'desc')->pluck('year')
            : Post::selectRaw('YEAR(created_at) as year')->distinct()->orderBy('year', 'desc')->pluck('year');

        return Inertia::render('ArchiveView', [
            'years' => $years,
            'posts' => ['data' => []],
            'allPosts' => [],
            'topics' => [],
            'currentTopic' => null,
            'currentPage' => 0,
            'hasMore' => false,
            'total' => 0,
            'archiveYear' => date('Y'),
        ]);
    }

    public function getYears()
    {
        try {
            $years = DB::connection()->getDriverName() === 'sqlite'
                ? Post::selectRaw("strftime('%Y', created_at) as year")->distinct()->orderBy('year', 'desc')->pluck('year')
                : Post::selectRaw("YEAR(created_at) as year")->distinct()->orderBy('year', 'desc')->pluck('year');

            Log::info('Years fetched successfully:', $years->toArray());

            return response()->json(['years' => $years]);
        } catch (\Exception $e) {
            Log::error('Error fetching years:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch years'], 500);
        }
    }

    public function show(Request $request, $year)
    {
        $topic = $request->input('topic');
        $page = $request->input('page', 1);
        $perPage = 6;

        // Base query depending on DB
        $query = DB::connection()->getDriverName() === 'sqlite'
            ? Post::select('id', 'title', 'content', 'topic', 'created_at', 'slug', 'image_path')
                ->whereRaw("strftime('%Y', created_at) = ?", [$year])
            : Post::select('id', 'title', 'content', 'topic', 'created_at', 'slug', 'image_path')
                ->whereYear('created_at', $year);

        if ($topic) {
            $query->where('topic', $topic);
        }

        // Paginated posts
        $posts = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Fix image URLs
        $posts->getCollection()->transform(function ($post) {
            $post->image_url = $this->formatImageUrl($post->image_path);
            return $post;
        });

        // All posts for the year
        $allPostsQuery = DB::connection()->getDriverName() === 'sqlite'
            ? Post::whereRaw("strftime('%Y', created_at) = ?", [$year])
            : Post::whereYear('created_at', $year);

        $allPosts = $allPostsQuery->orderBy('created_at', 'desc')->get();

        // Fix image URLs
        $allPosts->transform(function ($post) {
            $post->image_url = $this->formatImageUrl($post->image_path);
            return $post;
        });

        // All topics for the year
        $topics = DB::connection()->getDriverName() === 'sqlite'
            ? Post::whereRaw("strftime('%Y', created_at) = ?", [$year])->distinct()->pluck('topic')->toArray()
            : Post::whereYear('created_at', $year)->distinct()->pluck('topic')->toArray();

        return Inertia::render('ArchiveView', [
            'posts' => $posts,
            'allPosts' => $allPosts,
            'topics' => $topics,
            'currentTopic' => $topic,
            'currentPage' => $page - 1,
            'hasMore' => $posts->hasMorePages(),
            'total' => $posts->total(),
            'archiveYear' => (int)$year,
        ]);
    }

    // Helper method to generate correct image URL
    private function formatImageUrl(?string $imagePath): ?string
    {
        $imagePath = trim((string) $imagePath);

        if (!$imagePath) {
            return null;
        }

        // Already a full URL
        if (preg_match('/^https?:\/\//i', $imagePath)) {
            return $imagePath;
        }

        // Assume path like: /storage/uploads/xyz.jpg — generate full URL using url()
        return $imagePath ? asset('storage/' . $imagePath) : null;
    }


}
