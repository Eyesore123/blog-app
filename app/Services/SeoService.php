<?php

namespace App\Services;

use Illuminate\Support\Str;

class SeoService
{
    public static function forPost($post): array
    {
        return [
            'title' => $post->title,
            'description' => Str::limit(strip_tags($post->content), 160),
            'url' => route('posts.show', $post),
            'image' => $post->cover_image_url ?? null,
        ];
    }
}
