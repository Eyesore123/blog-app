<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ArchiveController;

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
Route::delete('api/comments/{comment_id}', [CommentController::class, 'destroy']);
Route::delete('/posts/{post_id}', [PostController::class, 'destroy'])->middleware('auth')->name('posts.destroy');
Route::get('/', [PostController::class, 'index']);
// Replace the index route with a redirect to the current year
Route::get('/archives', function() {
    return redirect()->route('archives.show', ['year' => date('Y')]);
})->name('archives.index');

// Keep the show route
Route::get('/archives/{year}', [ArchiveController::class, 'show'])->name('archives.show');


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
