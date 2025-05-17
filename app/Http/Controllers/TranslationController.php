<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TranslationController extends Controller
{
    public function translate(Request $request)
    {
        $request->validate([
            'q' => 'required|string',
            'source' => 'required|string',
            'target' => 'required|string',
        ]);

        $response = Http::post('https://libretranslate.de/translate', [
            'q' => $request->input('q'),
            'source' => $request->input('source'),
            'target' => $request->input('target'),
            'format' => 'text',
            'api_key' => '',  // Leave empty for public instance
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Translation failed'], 500);
        }

        return response()->json([
            'translatedText' => $response->json()['translatedText'] ?? '',
        ]);
    }
}
