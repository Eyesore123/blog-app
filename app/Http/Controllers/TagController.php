<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Http\Controllers\PostController;

class TagController extends Controller
{
    /**
     * Return all tags as a JSON array of tag names.
     */
    public function index(): JsonResponse
    {
        $tags = Tag::orderBy('name')->pluck('name');
        return response()->json($tags);
    }


    public function show($slug)
    {
        return app(PostController::class)->filterByTag($slug);
    }



}