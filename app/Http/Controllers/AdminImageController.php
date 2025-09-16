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

        $images = $this->getImages($perPage, $page);

        return response()->json([
            'data' => $images['images'],
            'current_page' => $images['current_page'],
            'per_page' => $images['per_page'],
            'total' => $images['total'],
            'next_page_url' => $images['next_page_url'],
        ]);
    }

    public function destroy($name)
    {
        $path = 'uploads/' . $name;
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            return response()->json(['success' => true]);
        }
        return response()->json(['error' => 'File not found'], 404);
    }

    private function getImages($perPage, $page)
    {
        $files = collect(Storage::disk('public')->files('uploads'))
            ->filter(fn($file) => Str::endsWith($file, ['.jpg', '.jpeg', '.png', '.gif', '.webp']))
            ->values();

        $total = $files->count();
        $files = $files->slice(($page - 1) * $perPage, $perPage);

        $images = $files->map(function ($file) {
            $name = basename($file);
            // Updated URL to use the new Laravel route
            $url = url('/uploads/' . $name);
            $post = Post::where('image_path', 'uploads/' . $name)->first();

            return [
                'name' => $name,
                'url' => $url,
                'postTitle' => $post ? $post->title : null,
            ];
        })->values();

        return [
            'images' => $images,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'next_page_url' => ($page * $perPage < $total) ? url("/admin/images?page=" . ($page + 1) . "&per_page=$perPage") : null,
        ];
    }

}
