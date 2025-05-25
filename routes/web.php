<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\RssFeedController;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Models\User;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\TranslationController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\DB;

// For production to clear 
// use Illuminate\Support\Facades\Artisan;

// Route::get('/run-config-cache', function () {
//     Artisan::call('config:cache');
//     return '✅ Config cache rebuilt!';
// });

// Image route test
Route::get('/img/{filename}', function ($filename) {
    $path = public_path($filename);
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    return response()->file($path, [
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->name('image.show');



// Anonymous login route, prevents brute force attacks
Route::middleware('throttle:5,1')->post('/anonymous-login', function () {
    $user = User::create([
        'name' => 'Anonymous' . Str::random(14),
        'email' => uniqid() . '@anon.local',
        'password' => bcrypt(Str::random(16)),
        'anonymous_id' => Str::uuid(),
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
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware(['auth', AdminMiddleware::class]);
Route::get('api/comments/post/{post_id}', [CommentController::class, 'comments.index']);
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

    // Not needed anymore because of identifier:
    
// Route::delete('/posts/{post_id}', [PostController::class, 'destroy'])->middleware('auth')->name('posts.destroy');

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

Route::get('feed', [RssFeedController::class, 'index'])->name('rss.feed');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

// Show single post

Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');

// Update routes

// Add this route for updating posts
Route::middleware('auth')->match(['put', 'post'], '/api/posts/{post}', [PostController::class, 'update'])->name('posts.update');


// Update a comment (for comment owners)
Route::middleware(['auth'])->put('/api/comments/{id}', [CommentController::class, 'update']);

// Delete a comment (for comment owners)
Route::middleware(['auth'])->delete('/api/comments/user/{id}', [CommentController::class, 'userDelete']);

// Delete a post:

Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

// Show comments by user

Route::get('api/comments/user/{user}', [CommentController::class, 'userComments'])->name('comments.userComments');

// Deactivate/activate user
Route::post('/admin/users/{user}/toggle', [AdminController::class, 'toggleUserStatus']);


// Delete user
Route::delete('/admin/users/{user}', [AdminController::class, 'deleteUser']);

// Account page routes

Route::middleware(['auth'])->group(function () {
    Route::get('/account', [AccountController::class, 'show'])->name('account');

    Route::get('/account/settings', [AccountController::class, 'show'])->name('account.settings');
    Route::post('/account/update-email', [AccountController::class, 'updateEmail']);
    Route::post('/account/update-password', [AccountController::class, 'updatePassword']);
    Route::post('/account/delete', [AccountController::class, 'deleteAccount']);
    Route::post('/account/subscribe-newsletter', [SubscriptionController::class, 'subscribe'])->name('account.subscribe');
    Route::post('/account/unsubscribe-newsletter', [SubscriptionController::class, 'unsubscribe'])->name('account.unsubscribe');
});

// Tag filter

Route::get('/posts/tag/{tag}', [PostController::class, 'filterByTag'])->name('posts.byTag');
Route::get('/posts/tag/{tag}', [PostController::class, 'filterByTag'])
     ->name('posts.byTag');

// Routes for forgot password and reset password

// Show form to request reset link
Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
    ->middleware('guest')
    ->name('password.request');

// Handle sending email
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');

// Show reset form
Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
    ->middleware('guest')
    ->name('password.reset');

// Handle actual reset
Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.update');

// Middleware (csp):

// Route::middleware(['csp'])->group(function () {
//     Route::get('/', [AdminController::class, 'index']);
//     Route::get('/posts', [PostController::class, 'index']);
// });

// Translation routes

Route::post('/translate', [TranslationController::class, 'translate'])
    ->name('translate')
    ->middleware('auth');  // Optional: protect route for authenticated users/admins only

// Store translation

// routes/api.php
Route::put('/posts/{post}/translation', [PostController::class, 'storeTranslation']);
