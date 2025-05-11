<?php

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
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

// Post routes
// Route::middleware(['auth:sanctum'])->group(function () {
//     Route::put('/posts/{post}', [PostController::class, 'update']);
// });

// Archive routes
Route::get('/archives/years', [ArchiveController::class, 'getYears']);

// Latest post routes
Route::get('/latest-post', [LatestPostController::class, 'show']);

// Recent activity feed routes
Route::get('/recent-activity', [RecentActivityController::class, 'index']);