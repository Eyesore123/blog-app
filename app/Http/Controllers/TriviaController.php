<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trivia;

class TriviaController extends Controller
{
    // Fetch all trivia as JSON (sorted)
    public function index()
    {
        return response()->json(Trivia::orderBy('sort_order', 'asc')->get());
    }

    // Add a new trivia item
    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|string|max:255'
        ]);

        // Set sort_order to the next available value
        $maxOrder = Trivia::max('sort_order') ?? 0;

        $trivia = Trivia::create([
            'label' => $request->label,
            'value' => $request->value,
            'sort_order' => $maxOrder + 1,
        ]);

        return response()->json($trivia, 201);
    }

    // Update trivia text
    public function update(Request $request, Trivia $trivia)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|string|max:255'
        ]);

        $trivia->update($request->only(['label', 'value']));
        return response()->json($trivia);
    }

    // Reorder trivia (expects array of IDs)
    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
        ]);

        foreach ($request->order as $index => $id) {
            Trivia::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['message' => 'Order updated']);
    }

    // Delete a trivia item
    public function destroy(Trivia $trivia)
    {
        $trivia->delete();
        return response()->json(null, 204);
    }
}
