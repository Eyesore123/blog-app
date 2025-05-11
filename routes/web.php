<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\MainPageController;
use App\Http\Controllers\RssFeedController;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Models\User;

Route::post('/anonymous-login', function () {
    $user = User::create([
        'name' => 'Anonymous' . Str::random(14), // now generates AnonymousXXXX...
        'email' => uniqid() . '@anon.local',
        'password' => bcrypt(Str::random(16)),
        'anonymous_id' => Str::uuid(), // now creates the anonymous_id properly
    ]);

    Auth::login($user);

    return redirect('/');
});

Route::get('api/archives/years', [ArchiveController::class, 'getYears']);
Route::get('/', [PostController::class, 'index'])->name('home');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/posts', [PostController::class, 'store'])
->middleware(['auth']);
Route::middleware('auth')->post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/admin', [AdminController::class, 'index']);
Route::get('api/comments/{post_id}', [CommentController::class, 'index']);
Route::get('/post/{identifier}', [PostController::class, 'show'])->name('post.show');
Route::get('/post/{id}/edit', [AdminController::class, 'edit'])->name('admin.edit');


// Route::post('/comments', 'CommentController@store')->middleware('comment-post');

// In your web.php file, change this:
// Route::middleware(['auth', 'throttle:comment-post'])
//     ->post('/api/comments', [CommentController::class, 'store']);
Route::middleware(['auth', 'throttle:10,1'])
    ->post('/api/comments', [CommentController::class, 'store']);

// Route::middleware(['auth', 'custom-throttle:5,1'])
//     ->post('/api/comments', [CommentController::class, 'store']);

// Route::get('api/comments/remaining', [CommentController::class, 'getRemaining']);

// Update this route to use auth middleware
Route::delete('api/comments/{comment_id}', [CommentController::class, 'destroy'])
    ->middleware('auth')
    ->name('comments.destroy');
Route::delete('/posts/{post_id}', [PostController::class, 'destroy'])->middleware('auth')->name('posts.destroy');
Route::get('/', [PostController::class, 'index']);

Route::get('/archives', function() {
    return redirect()->route('archives.show', ['year' => date('Y')]);
})->name('archives.index');

Route::get('/archives/{year}', [ArchiveController::class, 'show'])->name('archives.show');
// Error handling

// Direct render routes for error pages
Route::get('/error/404', function () {
    return Inertia::render('errors/Error', [
        'status' => 404,
        'message' => 'Page not found'
    ])->toResponse(request())->setStatusCode(404);
});

Route::get('/error/403', function () {
    return Inertia::render('errors/Error', [
        'status' => 403,
        'message' => 'Forbidden'
    ])->toResponse(request())->setStatusCode(403);
});

Route::get('/error/500', function () {
    return Inertia::render('errors/Error', [
        'status' => 500,
        'message' => 'Server error'
    ])->toResponse(request())->setStatusCode(500);
});

Route::get('/error/{code}', function ($code) {
    $code = (int)$code;
    $messages = [
        400 => 'Bad request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not found',
        419 => 'Page expired',
        429 => 'Too many requests',
        500 => 'Server error',
        503 => 'Service unavailable',
    ];
    
    return Inertia::render('errors/Error', [
        'status' => $code,
        'message' => $messages[$code] ?? 'Error'
    ])->toResponse(request())->setStatusCode($code);
});

Route::fallback(function () {
    return Inertia::render('errors/Error', [
        'status' => 404,
        'message' => 'Page not found'
    ]);
});

// Rss feed

Route::get('feed', [RssFeedController::class, 'index']);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');

// Update routes

// Add this route for updating posts
Route::middleware('auth')->match(['put', 'post'], '/api/posts/{post}', [PostController::class, 'update'])->name('posts.update');


// Update a comment (for comment owners)
Route::middleware(['auth'])->put('/api/comments/{id}', [CommentController::class, 'update']);

// Delete a comment (for comment owners)
Route::middleware(['auth'])->delete('/api/comments/user/{id}', [CommentController::class, 'userDelete']);
