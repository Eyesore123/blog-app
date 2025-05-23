<?php
// Simple security check - you should change this token
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

echo "<h1>Fix Admin Controller</h1>";

// Find all controllers that might be using the is_active column
$controllersDir = __DIR__ . '/../app/Http/Controllers';
if (!is_dir($controllersDir)) {
    echo "<p>Controllers directory not found at: $controllersDir</p>";
    exit;
}

echo "<h2>Searching for Controllers Using 'is_active'</h2>";
$foundControllers = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($controllersDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        if (strpos($content, 'is_active') !== false) {
            $foundControllers[] = [
                'path' => $file->getPathname(),
                'name' => $file->getFilename(),
                'content' => $content
            ];
        }
    }
}

if (empty($foundControllers)) {
    echo "<p>No controllers found that use the 'is_active' column.</p>";
    
    // Let's check for admin controllers or user management controllers
    echo "<h2>Searching for Admin or User Management Controllers</h2>";
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($controllersDir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            $filename = $file->getFilename();
            if (
                strpos($filename, 'Admin') !== false || 
                strpos($filename, 'User') !== false || 
                strpos($content, 'admin') !== false || 
                strpos($content, 'users') !== false
            ) {
                $foundControllers[] = [
                    'path' => $file->getPathname(),
                    'name' => $file->getFilename(),
                    'content' => $content
                ];
            }
        }
    }
}

if (empty($foundControllers)) {
    echo "<p>No admin or user management controllers found.</p>";
    
    // Create a basic AdminController
    $adminControllerPath = $controllersDir . '/AdminController.php';
    $adminControllerContent = <<<EOD
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function index()
    {
        \$users = User::select('id', 'name', 'email', 'is_active')->get();
        
        return Inertia::render('Admin/Dashboard', [
            'users' => \$users
        ]);
    }
    
    public function toggleUserStatus(User \$user)
    {
        \$user->is_active = !\$user->is_active;
        \$user->save();
        
        return redirect()->back()->with('success', 'User status updated successfully');
    }
    
    public function deleteUser(User \$user)
    {
        \$user->delete();
        
        return redirect()->back()->with('success', 'User deleted successfully');
    }
}
EOD;

    // Write the AdminController file
    if (file_put_contents($adminControllerPath, $adminControllerContent)) {
        echo "<p>Created a basic AdminController at: $adminControllerPath</p>";
    } else {
        echo "<p>Failed to create AdminController. Check permissions.</p>";
    }
} else {
    echo "<p>Found " . count($foundControllers) . " controller(s) that might be using the 'is_active' column:</p>";
    
    foreach ($foundControllers as $controller) {
        echo "<h3>{$controller['name']}</h3>";
        echo "<p>Path: {$controller['path']}</p>";
        
        // Create a backup
        $backupPath = $controller['path'] . '.backup-' . date('Y-m-d-H-i-s');
        file_put_contents($backupPath, $controller['content']);
        echo "<p>Created backup at: $backupPath</p>";
        
        // Check if the controller has a method that uses is_active
        if (strpos($controller['content'], 'is_active') !== false) {
            echo "<p>This controller already uses the 'is_active' column. No changes needed.</p>";
        } else {
            // Add is_active to the controller if it's handling users
            if (
                strpos($controller['content'], 'User::') !== false || 
                strpos($controller['content'], 'users') !== false
            ) {
                // Modify the controller to include is_active
                $modifiedContent = $controller['content'];
                
                // If it's selecting users, add is_active to the select
                $modifiedContent = preg_replace(
                    '/User::select\([^)]*\)/',
                    'User::select(\'id\', \'name\', \'email\', \'is_active\')',
                    $modifiedContent
                );
                
                // Write the modified controller
                if (file_put_contents($controller['path'], $modifiedContent)) {
                    echo "<p>Updated controller to include 'is_active' in user selection.</p>";
                } else {
                    echo "<p>Failed to update controller. Check permissions.</p>";
                }
            } else {
                echo "<p>This controller doesn't appear to handle users directly. No changes made.</p>";
            }
        }
    }
}

// Check if there's a route for the admin dashboard
$routesPath = __DIR__ . '/../routes/web.php';
if (file_exists($routesPath)) {
    $routesContent = file_get_contents($routesPath);
    
    // Check if there's an admin route
    if (strpos($routesContent, '/admin') === false) {
        echo "<h2>Adding Admin Routes</h2>";
        
        // Create a backup
        $backupPath = $routesPath . '.backup-' . date('Y-m-d-H-i-s');
        file_put_contents($backupPath, $routesContent);
        echo "<p>Created backup of routes file at: $backupPath</p>";
        
        // Add admin routes
        $adminRoutes = <<<EOD

// Admin routes
Route::middleware(['auth'])->group(function () {
    Route::get('/admin', [App\\Http\\Controllers\\AdminController::class, 'index'])->name('admin.dashboard');
    Route::post('/admin/users/{user}/toggle', [App\\Http\\Controllers\\AdminController::class, 'toggleUserStatus'])->name('admin.users.toggle');
    Route::delete('/admin/users/{user}', [App\\Http\\Controllers\\AdminController::class, 'deleteUser'])->name('admin.users.delete');
});
EOD;

        $newRoutesContent = $routesContent . $adminRoutes;
        
        // Write the modified routes file
        if (file_put_contents($routesPath, $newRoutesContent)) {
            echo "<p>Added admin routes to the routes file.</p>";
        } else {
            echo "<p>Failed to update routes file. Check permissions.</p>";
        }
    } else {
        echo "<p>Admin routes already exist in the routes file.</p>";
    }
} else {
    echo "<p>Routes file not found at: $routesPath</p>";
}

echo "<p>Done. <a href='/admin'>Go to admin dashboard</a></p>";
