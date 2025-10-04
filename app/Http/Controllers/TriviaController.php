<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trivia;

class TriviaController extends Controller
{
    // Fetch all trivia as JSON
    public function index()
    {
        return response()->json(Trivia::all());
    }

    // Add a new trivia item
    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|string|max:255'
        ]);

        $trivia = Trivia::create([
            'label' => $request->label,
            'value' => $request->value,
        ]);

        return response()->json($trivia, 201);
    }

    // Update an existing trivia item
    public function update(Request $request, Trivia $trivia)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|string|max:255'
        ]);

        $trivia->update($request->only(['label', 'value']));
        return response()->json($trivia);
    }

    // Delete a trivia item
    public function destroy(Trivia $trivia)
    {
        $trivia->delete();
        return response()->json(null, 204);
    }
}
