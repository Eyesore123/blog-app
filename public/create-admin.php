<?php

// Simple security check - you should change this token
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

// Get the DATABASE_URL
$databaseUrl = getenv('DATABASE_URL');

if (!$databaseUrl) {
    echo "DATABASE_URL is not set!";
    exit(1);
}

try {
    // Connect to the database
    $pdo = new PDO($databaseUrl);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Creating Admin User</h1>";
    
    // Check if admin user already exists
    $email = 'admin@example.com'; // Change this to your email
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $adminExists = $stmt->fetchColumn() > 0;
    
    if ($adminExists) {
        echo "<p>Admin user with email $email already exists.</p>";
    } else {
        // Generate a secure random password
        $password = bin2hex(random_bytes(8)); // 16 character random password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $now = date('Y-m-d H:i:s');
        
        // Create admin user
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, is_admin, email_verified_at, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            'Admin User',
            $email,
            $hashedPassword,
            true,
            $now,
            $now,
            $now
        ]);
        
        echo "<p>Admin user created successfully!</p>";
        echo "<p>Email: $email</p>";
        echo "<p>Password: $password</p>";
        echo "<p><strong>Save this password! It won't be shown again.</strong></p>";
    }
    
} catch (PDOException $e) {
    echo "<h1>Error</h1>";
    echo "<p>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
