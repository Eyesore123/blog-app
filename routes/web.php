<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CommentController;

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

Route::middleware('auth')->post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/admin', [AdminController::class, 'index']);  // ✅ clean & correct

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
