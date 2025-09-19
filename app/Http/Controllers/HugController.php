<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class HugController extends Controller
{
    /**
     * Get the current hug count.
     */
    public function index()
    {
        $hug = DB::table('hugs')->first();
        return response()->json(['count' => $hug?->count ?? 0]);
    }

    /**
     * Increment hug count.
     */
    public function store(Request $request)
    {
        $key = 'hug:' . $request->ip();
        $maxAttempts = 1;
        $decaySeconds = 3;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'error' => 'Slow down, you can send a new hug in a few seconds!'
            ], 429);
        }

        RateLimiter::hit($key, $decaySeconds);

        // Get the single row (we assume only one row exists)
        $hug = DB::table('hugs')->first();

        if ($hug) {
            DB::table('hugs')->where('id', $hug->id)->increment('count');
        } else {
            DB::table('hugs')->insert([
                'count' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $newCount = DB::table('hugs')->first()->count;

        return response()->json(['count' => $newCount]);
    }
}
