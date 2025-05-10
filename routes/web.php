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

Route::get('api/archives/years', [ArchiveController::class, 'getYears']);
Route::get('/', [PostController::class, 'index'])->name('home');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/anonymous-login', function () {
    $user = \App\Models\User::create([
        'name' => 'Anonymous',
        'email' => uniqid() . '@anon.local',
        'password' => bcrypt(str()->random(16)),
    ]);

    Auth::login($user);

    return redirect('/');
});
Route::post('/posts', [PostController::class, 'store'])
->middleware(['auth']);
Route::middleware('auth')->post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/admin', [AdminController::class, 'index']);
Route::get('api/comments/{post_id}', [CommentController::class, 'index']);
Route::get('/post/{identifier}', [PostController::class, 'show'])->name('post.show');
Route::get('/post/{id}/edit', [AdminController::class, 'edit'])->name('admin.edit');
Route::post('api/comments', [CommentController::class, 'store']);
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
