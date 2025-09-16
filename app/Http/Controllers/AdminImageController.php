<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Post;

class AdminImageController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $page = $request->input('page', 1);

        $files = collect(Storage::files('public/uploads'))
            ->filter(fn($file) => Str::endsWith($file, ['.jpg', '.jpeg', '.png', '.gif', '.webp']))
            ->values();

        $total = $files->count();
        $files = $files->slice(($page - 1) * $perPage, $perPage);

        $images = $files->map(function ($file) {
            $name = basename($file);
            $url = Storage::url($file);
            $size = Storage::size($file);
            $post = Post::where('image_path', 'uploads/' . $name)->first();

            return [
                'name' => $name,
                'url' => $url,
                'size' => $size,
                'postTitle' => $post ? $post->title : null,
            ];
        })->values();

        return response()->json([
            'data' => $images,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'next_page_url' => ($page * $perPage < $total) ? url("/admin/images?page=" . ($page + 1) . "&per_page=$perPage") : null,
        ]);
    }

    public function destroy($name)
    {
        $path = 'public/uploads/' . $name;
        if (Storage::exists($path)) {
            Storage::delete($path);
            return response()->json(['success' => true]);
        }
        return response()->json(['error' => 'File not found'], 404);
    }
}