<?php

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommentController;
use App\Models\User;
use App\Http\Controllers\PostController;
use App\Http\Controllers\LatestPostController;
use App\Http\Controllers\RecentActivityController;

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

Route::get('/comments/{postId}', [CommentController::class, 'index']);
Route::get('/comments/{id}', [CommentController::class, 'show']);;
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/comments', [CommentController::class, 'store']);
    Route::put('/posts/{post}', [PostController::class, 'update']);
});
Route::get('/archives/years', [ArchiveController::class, 'getYears']);

// Latest post routes

Route::get('/latest-post', [LatestPostController::class, 'show']);

// Routes for recent activity feed

Route::get('/recent-activity', [RecentActivityController::class, 'index']);