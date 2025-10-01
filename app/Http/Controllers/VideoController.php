<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class VideoController extends Controller
{
    /**
     * Stream a video file.
     */
    public function show($filename)
    {
        $path = storage_path("app/public/uploads/videos/{$filename}");

        if (!file_exists($path)) {
            abort(404, 'Video not found.');
        }

        $mime = mime_content_type($path);
        $size = filesize($path);

        $stream = fopen($path, 'rb');

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Length' => $size,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }

    /**
     * Normalize a video model for frontend.
     */
    private function transformVideo($video)
    {
        return [
            'name' => $video['name'],
            'url'  => Storage::url("uploads/videos/{$video['name']}"),
            'uploaded_at' => $video['uploaded_at'] ?? now(),
        ];
    }

    /**
     * List all uploaded videos.
     */
    public function index()
    {
        $files = collect(Storage::disk('public')->files('uploads/videos'))
            ->map(function($path) {
                $filename = basename($path);
                return [
                    'name' => $filename,
                    'url' => Storage::url("uploads/videos/{$filename}"),
                    'uploaded_at' => date('Y-m-d H:i:s', Storage::disk('public')->lastModified($path)),
                ];
            })
            ->sortByDesc('uploaded_at')
            ->values(); // reindex array

        return response()->json([
            'data' => $files, // frontend expects res.data.data
            'total' => $files->count(),
            'last_page' => 1,
            'current_page' => 1,
        ]);
    }

    /**
     * Upload a video.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,webm,ogg|max:51200', // 50MB max
        ]);

        if ($request->hasFile('video')) {
            $file = $request->file('video');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('uploads/videos', $filename, 'public');

            // Return full info for frontend
            return response()->json([
                'success' => true,
                'video' => [
                    'name' => $filename,
                    'url' => Storage::url($path), // /storage/uploads/videos/filename.mp4
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No video uploaded.'
        ], 400);
    }

    /**
     * Delete a video.
     */
    public function destroy($name)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $path = "uploads/videos/{$name}";
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            Log::info("Video deleted: {$path}");
            return response()->json(['success' => true, 'message' => 'Video deleted']);
        }

        return response()->json(['error' => 'Video not found'], 404);
    }
}
