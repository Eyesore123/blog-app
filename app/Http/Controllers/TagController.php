<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\JsonResponse;

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
}