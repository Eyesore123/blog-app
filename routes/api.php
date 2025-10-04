<?php

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LatestPostController;
use App\Http\Controllers\RecentActivityController;
use App\Http\Controllers\ArchiveController;
use App\Models\User;
use App\Http\Controllers\Api\InfoBannerController;
use App\Http\Controllers\TriviaController;
use Inertia\Inertia;
use App\Models\Trivia;
use App\Http\Controllers\NewsController;

// Route for info banner in the backend:

Route::get('/info-banner', [InfoBannerController::class, 'index']);
Route::post('/admin/info-banner', [InfoBannerController::class, 'update']);

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

// Additional routes for portfolio integration + all posts component. can't remember if there was a reason to take 10
Route::get('/blog/posts', function () {
    $posts = \App\Models\Post::with(['user', 'tags'])
        ->where('published', true)
        ->orderBy('created_at', 'desc')
        // ->take(10)
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

Route::get('/trivia', function () {
    return response()->json(Trivia::all());
});

// Admin API for managing trivia
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/trivia', [TriviaController::class, 'index']);       // fetch all for admin
    Route::post('/trivia', [TriviaController::class, 'store']);      // add
    Route::put('/trivia/{trivia}', [TriviaController::class, 'update']);  // edit
    Route::delete('/trivia/{trivia}', [TriviaController::class, 'destroy']); // delete
});

// Admin API for managing news
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/news', [NewsController::class, 'adminIndex']);       // fetch all for admin
    Route::post('/news', [NewsController::class, 'store']);          // add
    Route::put('/news/{news}', [NewsController::class, 'update']);   // edit
    Route::delete('/news/{news}', [NewsController::class, 'destroy']); // delete
});
