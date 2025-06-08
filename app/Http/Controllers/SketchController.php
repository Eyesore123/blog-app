<?php

namespace App\Http\Controllers;

use App\Models\Sketch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SketchController extends Controller
{
    public function index()
    {
        // Only admin can see all sketches (route is protected by middleware)
        // Removed the call to $this->authorize('admin');
        return response()->json(
            Sketch::with('user')->latest()->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $sketch = Sketch::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'content' => $request->content,
        ]);

        return response()->json($sketch, 201);
    }

    public function destroy(Sketch $sketch)
    {
        $sketch->delete();
        return response()->json(['success' => true]);
    }
}