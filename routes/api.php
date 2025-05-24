<?php

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LatestPostController;
use App\Http\Controllers\RecentActivityController;
use App\Http\Controllers\ArchiveController;
use App\Models\User;

// Login route
Route::post('login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);
    
    $user = User::where('email', $request->email)->first();
    
    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }
    
    return response()->json([
        'token' => $user->createToken('YourAppName')->plainTextToken,
    ]);
});

// Comment routes
Route::get('/comments/{postId}', [CommentController::class, 'index']);
Route::get('/comments/{id}', [CommentController::class, 'show']);

Route::middleware(['auth:sanctum', 'throttle:comment-post'])->group(function () {
    Route::post('/comments', [CommentController::class, 'store']);
});

// Archive routes
Route::get('/archives/years', [ArchiveController::class, 'getYears']);

// Latest post routes
Route::get('/latest-post', [LatestPostController::class, 'show']);

// Recent activity feed routes
Route::get('/recent-activity', [RecentActivityController::class, 'index']);

// Additional routes for portfolio integration
Route::get('/blog/posts', function () {
    $posts = \App\Models\Post::with(['user', 'tags'])
        ->where('published', true)
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get();
        
    return response()->json($posts);
});

Route::get('/blog/latest', function () {
    $post = \App\Models\Post::with(['user', 'tags'])
        ->where('published', true)
        ->orderBy('created_at', 'desc')
        ->first();
        
    return response()->json($post);
});
