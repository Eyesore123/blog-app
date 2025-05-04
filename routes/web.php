<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PostController;

Route::get('/', [PostController::class, 'index'])->name('home');

Route::post('/logout', function () {
    auth()->logout();
    return redirect('/');
})->name('logout');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
