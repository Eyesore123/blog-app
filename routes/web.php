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

// Temporary route to check admin user - REMOVE AFTER USE!
Route::get('/check-admin/{email}', function ($email) {
    try {
        $user = \App\Models\User::where('email', $email)->first();
        
        if (!$user) {
            return "User with email $email not found.";
        }
        
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => (bool)$user->is_admin,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            // Don't return password hash for security reasons
        ];
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

// Temporary route to update admin password - REMOVE AFTER USE!
Route::get('/update-admin/{email}/{password}/{token}', function ($email, $password, $token) {
    // Check if the token matches a randomly generated token stored in the environment
    $validToken = env('ADMIN_SETUP_TOKEN');
    
    if (!$validToken || $token !== $validToken) {
        return response('Unauthorized', 401);
    }
    
    try {
        $user = \App\Models\User::where('email', $email)->first();
        
        if (!$user) {
            return "User with email $email not found.";
        }
        
        // Update the user's password
        $user->password = bcrypt($password);
        $user->save();
        
        return "Password updated successfully for user $email.";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

// Temporary route to test authentication - REMOVE AFTER USE!
Route::get('/test-auth/{email}/{password}', function ($email, $password) {
    try {
        // Attempt to authenticate the user
        $credentials = [
            'email' => $email,
            'password' => $password
        ];
        
        $authenticated = \Illuminate\Support\Facades\Auth::attempt($credentials);
        
        if ($authenticated) {
            $user = \Illuminate\Support\Facades\Auth::user();
            \Illuminate\Support\Facades\Auth::logout();
            
            return [
                'authenticated' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_admin' => (bool)$user->is_admin
                ]
            ];
        } else {
            // Check if the user exists
            $user = \App\Models\User::where('email', $email)->first();
            
            if (!$user) {
                return [
                    'authenticated' => false,
                    'reason' => 'User not found'
                ];
            }
            
            return [
                'authenticated' => false,
                'reason' => 'Invalid password',
                'user_exists' => true
            ];
        }
    } catch (\Exception $e) {
        return [
            'authenticated' => false,
            'error' => $e->getMessage()
        ];
    }
});

// Temporary route to check database tables - REMOVE AFTER USE!
Route::get('/check-tables', function () {
    try {
        $databaseUrl = env('DATABASE_URL');
        
        if (!$databaseUrl) {
            return "DATABASE_URL is not set!";
        }
        
        $pdo = new PDO($databaseUrl);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Get all tables in the public schema
        $stmt = $pdo->query("
            SELECT table_name 
            FROM information_schema.tables 
            WHERE table_schema = 'public'
            ORDER BY table_name
        ");
        
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($tables)) {
            return "No tables found in the database.";
        }
        
        return [
            'tables' => $tables,
            'count' => count($tables)
        ];
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

// Temporary route to run migrations - REMOVE AFTER USE!
Route::get('/run-migrations/{token}', function ($token) {
    // Check if the token matches a randomly generated token stored in the environment
    $validToken = env('ADMIN_SETUP_TOKEN');
    
    if (!$validToken || $token !== $validToken) {
        return response('Unauthorized', 401);
    }
    
    try {
        // Run migrations
        \Illuminate\Support\Facades\Artisan::call('migrate', [
            '--force' => true
        ]);
        
        return \Illuminate\Support\Facades\Artisan::output();
    } catch (\Exception $e) {
        return "Error running migrations: " . $e->getMessage();
    }
});

// Temporary route to create tables manually - REMOVE AFTER USE!
Route::get('/create-tables/{token}', function ($token) {
    // Check if the token matches a randomly generated token stored in the environment
    $validToken = env('ADMIN_SETUP_TOKEN');
    
    if (!$validToken || $token !== $validToken) {
        return response('Unauthorized', 401);
    }
    
    try {
        $databaseUrl = env('DATABASE_URL');
        
        if (!$databaseUrl) {
            return "DATABASE_URL is not set!";
        }
        
        $pdo = new PDO($databaseUrl);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create users table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                email_verified_at TIMESTAMP NULL,
                password VARCHAR(255) NOT NULL,
                is_admin BOOLEAN NOT NULL DEFAULT FALSE,
                remember_token VARCHAR(100) NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )
        ");
        
        // Create password_reset_tokens table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS password_reset_tokens (
                email VARCHAR(255) PRIMARY KEY,
                token VARCHAR(255) NOT NULL,
                created_at TIMESTAMP NULL
            )
        ");
        
        // Create migrations table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id SERIAL PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INTEGER NOT NULL
            )
        ");
        
        // Create personal_access_tokens table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS personal_access_tokens (
                id SERIAL PRIMARY KEY,
                tokenable_type VARCHAR(255) NOT NULL,
                tokenable_id BIGINT NOT NULL,
                name VARCHAR(255) NOT NULL,
                token VARCHAR(64) NOT NULL UNIQUE,
                abilities TEXT NULL,
                last_used_at TIMESTAMP NULL,
                expires_at TIMESTAMP NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )
        ");
        
        // Create failed_jobs table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS failed_jobs (
                id SERIAL PRIMARY KEY,
                uuid VARCHAR(255) NOT NULL UNIQUE,
                connection TEXT NOT NULL,
                queue TEXT NOT NULL,
                payload TEXT NOT NULL,
                exception TEXT NOT NULL,
                failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Create posts table (if your app has posts)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS posts (
                id SERIAL PRIMARY KEY,
                user_id BIGINT NOT NULL,
                title VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
        
        return "Tables created successfully!";
    } catch (\Exception $e) {
        return "Error creating tables: " . $e->getMessage();
    }
});


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
