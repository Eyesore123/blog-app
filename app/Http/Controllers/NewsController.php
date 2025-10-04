<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\News;
use Inertia\Inertia;

class NewsController extends Controller
{
    // Fetch all news for public /news page
    public function index()
    {
        $news = News::orderBy('created_at', 'desc')->get();
        return Inertia::render('NewsPage', ['news' => $news]);
    }

    // Fetch only 2 most recent news for sidebar component
    public function latest(Request $request)
    {
        if ($request->query('all')) {
            return response()->json(News::orderBy('created_at','desc')->get());
        }

        return response()->json(News::orderBy('created_at','desc')->take(2)->get());
    }


    // Admin API: fetch all news
    public function adminIndex()
    {
        return response()->json(News::orderBy('created_at', 'desc')->get());
    }

    // Admin API: store new news
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $news = News::create($request->only('title', 'content'));

        return response()->json($news);
    }

    // Admin API: update existing news
    public function update(Request $request, News $news)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $news->update($request->only('title', 'content'));

        return response()->json($news);
    }

    // Admin API: delete news
    public function destroy(News $news)
    {
        $news->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
