<?php
// Simple security check - you should change this token
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

echo "<h1>Fix User Model</h1>";

// Check if the User model exists
$userModelPath = __DIR__ . '/../app/Models/User.php';
if (!file_exists($userModelPath)) {
    echo "<p>User model not found at: $userModelPath</p>";
    exit;
}

// Read the current User model
$userModelContent = file_get_contents($userModelPath);

// Create a backup
$backupPath = $userModelPath . '.backup-' . date('Y-m-d-H-i-s');
file_put_contents($backupPath, $userModelContent);
echo "<p>Created backup of User model at: $backupPath</p>";

// Show the current User model
echo "<h2>Current User Model</h2>";
echo "<pre>" . htmlspecialchars($userModelContent) . "</pre>";

// Check if the is_active attribute is in the $fillable array
if (strpos($userModelContent, "'is_active'") === false) {
    echo "<p>The 'is_active' attribute is not in the \$fillable array. Adding it...</p>";
    
    // Find the $fillable array
    $fillablePos = strpos($userModelContent, 'protected $fillable = [');
    if ($fillablePos !== false) {
        $fillableEndPos = strpos($userModelContent, '];', $fillablePos);
        if ($fillableEndPos !== false) {
            // Add the is_active attribute to the $fillable array
            $newContent = substr($userModelContent, 0, $fillableEndPos);
            $newContent .= "'is_active', ";
            $newContent .= substr($userModelContent, $fillableEndPos);
            
            // Write the modified User model
            if (file_put_contents($userModelPath, $newContent)) {
                echo "<p>User model updated to include 'is_active' in the \$fillable array.</p>";
                $userModelContent = $newContent;
            } else {
                echo "<p>Failed to update User model. Check permissions.</p>";
            }
        } else {
            echo "<p>Could not find the end of the \$fillable array in the User model.</p>";
        }
    } else {
        echo "<p>Could not find the \$fillable array in the User model.</p>";
    }
}

// Check if the is_active attribute is in the $casts array
if (strpos($userModelContent, "'is_active' => 'boolean'") === false) {
    echo "<p>The 'is_active' attribute is not in the \$casts array. Adding it...</p>";
    
    // Find the $casts array
    $castsPos = strpos($userModelContent, 'protected $casts = [');
    if ($castsPos !== false) {
        $castsEndPos = strpos($userModelContent, '];', $castsPos);
        if ($castsEndPos !== false) {
            // Add the is_active attribute to the $casts array
            $newContent = substr($userModelContent, 0, $castsEndPos);
            $newContent .= "'is_active' => 'boolean', ";
            $newContent .= substr($userModelContent, $castsEndPos);
            
            // Write the modified User model
            if (file_put_contents($userModelPath, $newContent)) {
                echo "<p>User model updated to include 'is_active' in the \$casts array.</p>";
                $userModelContent = $newContent;
            } else {
                echo "<p>Failed to update User model. Check permissions.</p>";
            }
        } else {
            echo "<p>Could not find the end of the \$casts array in the User model.</p>";
        }
    } else {
        echo "<p>Could not find the \$casts array in the User model.</p>";
    }
}

// Show the updated User model
echo "<h2>Updated User Model</h2>";
echo "<pre>" . htmlspecialchars($userModelContent) . "</pre>";

echo "<p>Done. <a href='/admin'>Go to admin dashboard</a></p>";
