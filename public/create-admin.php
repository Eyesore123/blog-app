<?php
// Security check - added automatically
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    http_response_code(404);
    echo "Not Found";
    exit;
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Create Admin User - PostgreSQL</title>
    <link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'>
</head>
<body class='bg-gray-100 p-8'>
    <div class='max-w-2xl mx-auto'>
        <h1 class='text-2xl font-bold mb-6'>👑 Create Admin User (PostgreSQL)</h1>";

// Get database credentials from environment
$dbHost = getenv('PGHOST');
$dbPort = getenv('PGPORT') ?: '5432';
$dbName = getenv('PGDATABASE');
$dbUser = getenv('PGUSER');
$dbPass = getenv('PGPASSWORD');

if (!$dbHost || !$dbName || !$dbUser || !$dbPass) {
    echo "<div class='bg-red-100 border border-red-400 p-4 rounded mb-4'>
            <h3 class='font-bold text-red-800'>❌ Missing Database Configuration</h3>
            <p class='text-red-700'>Required environment variables not found:</p>
            <ul class='list-disc list-inside text-sm text-red-600 mt-2'>
                <li>PGHOST: " . ($dbHost ? '✓' : '❌') . "</li>
                <li>PGDATABASE: " . ($dbName ? '✓' : '❌') . "</li>
                <li>PGUSER: " . ($dbUser ? '✓' : '❌') . "</li>
                <li>PGPASSWORD: " . ($dbPass ? '✓' : '❌') . "</li>
            </ul>
          </div>";
    echo "</div></body></html>";
    exit;
}

// Try to connect using PDO with PostgreSQL
try {
    $dsn = "pgsql:host=$dbHost;port=$dbPort;dbname=$dbName";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "<div class='bg-green-100 border border-green-400 p-4 rounded mb-4'>
            <h3 class='font-bold text-green-800'>✅ Database Connection Successful</h3>
            <p class='text-green-700'>Connected to PostgreSQL database: $dbName</p>
          </div>";
    
} catch (PDOException $e) {
    echo "<div class='bg-red-100 border border-red-400 p-4 rounded mb-4'>
            <h3 class='font-bold text-red-800'>❌ Database Connection Failed</h3>
            <p class='text-red-700'>Error: " . htmlspecialchars($e->getMessage()) . "</p>
          </div>";
    echo "</div></body></html>";
    exit;
}

// Check if users table exists
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'users'");
    $tableExists = $stmt->fetchColumn() > 0;
    
    if (!$tableExists) {
        echo "<div class='bg-yellow-100 border border-yellow-400 p-4 rounded mb-4'>
                <h3 class='font-bold text-yellow-800'>⚠️ Users Table Missing</h3>
                <p class='text-yellow-700'>The users table doesn't exist. Creating it now...</p>
              </div>";
        
        // Create users table
        $createTable = "
            CREATE TABLE users (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                email_verified_at TIMESTAMP NULL,
                password VARCHAR(255) NOT NULL,
                is_admin BOOLEAN DEFAULT FALSE,
                is_active BOOLEAN DEFAULT TRUE,
                remember_token VARCHAR(100) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ";
        
        $pdo->exec($createTable);
        
        echo "<div class='bg-green-100 border border-green-400 p-4 rounded mb-4'>
                <h3 class='font-bold text-green-800'>✅ Users Table Created</h3>
                <p class='text-green-700'>Users table has been created successfully.</p>
              </div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='bg-red-100 border border-red-400 p-4 rounded mb-4'>
            <h3 class='font-bold text-red-800'>❌ Table Check Failed</h3>
            <p class='text-red-700'>Error: " . htmlspecialchars($e->getMessage()) . "</p>
          </div>";
}

// Handle form submission
if ($_POST) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($name) || empty($email) || empty($password)) {
        echo "<div class='bg-red-100 border border-red-400 p-4 rounded mb-4'>
                <h3 class='font-bold text-red-800'>❌ Validation Error</h3>
                <p class='text-red-700'>All fields are required.</p>
              </div>";
    } else {
        try {
            // Check if user already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                echo "<div class='bg-yellow-100 border border-yellow-400 p-4 rounded mb-4'>
                        <h3 class='font-bold text-yellow-800'>⚠️ User Already Exists</h3>
                        <p class='text-yellow-700'>A user with email '$email' already exists.</p>
                      </div>";
            } else {
                // Create new admin user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("
                    INSERT INTO users (name, email, password, is_admin, is_active, created_at, updated_at) 
                    VALUES (?, ?, ?, TRUE, TRUE, NOW(), NOW())
                ");
                
                if ($stmt->execute([$name, $email, $hashedPassword])) {
                    $userId = $pdo->lastInsertId();
                    
                    echo "<div class='bg-green-100 border border-green-400 p-4 rounded mb-4'>
                            <h3 class='font-bold text-green-800'>✅ Admin User Created Successfully!</h3>
                            <div class='text-green-700 mt-2'>
                                <p><strong>User ID:</strong> $userId</p>
                                <p><strong>Name:</strong> $name</p>
                                <p><strong>Email:</strong> $email</p>
                                <p><strong>Admin Status:</strong> Yes</p>
                                <p><strong>Account Status:</strong> Active</p>
                            </div>
                          </div>";
                } else {
                    echo "<div class='bg-red-100 border border-red-400 p-4 rounded mb-4'>
                            <h3 class='font-bold text-red-800'>❌ Failed to Create User</h3>
                            <p class='text-red-700'>Database insert failed.</p>
                          </div>";
                }
            }
            
        } catch (PDOException $e) {
            echo "<div class='bg-red-100 border border-red-400 p-4 rounded mb-4'>
                    <h3 class='font-bold text-red-800'>❌ Database Error</h3>
                    <p class='text-red-700'>Error: " . htmlspecialchars($e->getMessage()) . "</p>
                  </div>";
        }
    }
}

// Show existing users
try {
    $stmt = $pdo->query("SELECT id, name, email, is_admin, is_active, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
    
    if ($users) {
        echo "<div class='bg-white p-6 rounded-lg shadow mb-6'>
                <h3 class='text-lg font-semibold mb-4'>👥 Existing Users</h3>
                <div class='overflow-x-auto'>
                    <table class='w-full text-sm'>
                        <thead>
                            <tr class='border-b'>
                                <th class='text-left p-2'>ID</th>
                                <th class='text-left p-2'>Name</th>
                                <th class='text-left p-2'>Email</th>
                                <th class='text-left p-2'>Admin</th>
                                <th class='text-left p-2'>Active</th>
                                <th class='text-left p-2'>Created</th>
                            </tr>
                        </thead>
                        <tbody>";
        
        foreach ($users as $user) {
            $adminBadge = $user['is_admin'] ? '<span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Admin</span>' : '<span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">User</span>';
            $activeBadge = $user['is_active'] ? '<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Active</span>' : '<span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Inactive</span>';
            
            echo "<tr class='border-b'>
                    <td class='p-2'>{$user['id']}</td>
                    <td class='p-2'>{$user['name']}</td>
                    <td class='p-2'>{$user['email']}</td>
                    <td class='p-2'>$adminBadge</td>
                    <td class='p-2'>$activeBadge</td>
                    <td class='p-2'>" . date('M j, Y', strtotime($user['created_at'])) . "</td>
                  </tr>";
        }
        
        echo "      </tbody>
                    </table>
                </div>
              </div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='bg-yellow-100 border border-yellow-400 p-4 rounded mb-4'>
            <h3 class='font-bold text-yellow-800'>⚠️ Could not load existing users</h3>
            <p class='text-yellow-700'>Error: " . htmlspecialchars($e->getMessage()) . "</p>
          </div>";
}

?>

        <!-- Create Admin Form -->
        <div class='bg-white p-6 rounded-lg shadow'>
            <h3 class='text-lg font-semibold mb-4'>➕ Create New Admin User</h3>
            <form method='POST' class='space-y-4'>
                <div>
                    <label class='block text-sm font-medium text-gray-700 mb-1'>Full Name</label>
                    <input type='text' name='name' required 
                           class='w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500'
                           placeholder='Enter full name'>
                </div>
                
                <div>
                    <label class='block text-sm font-medium text-gray-700 mb-1'>Email Address</label>
                    <input type='email' name='email' required 
                           class='w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500'
                           placeholder='Enter email address'>
                </div>
                
                <div>
                    <label class='block text-sm font-medium text-gray-700 mb-1'>Password</label>
                    <input type='password' name='password' required 
                           class='w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500'
                           placeholder='Enter secure password'>
                </div>
                
                <button type='submit' 
                        class='w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 transition-colors'>
                    👑 Create Admin User
                </button>
            </form>
        </div>
        
        <div class='mt-6 text-center'>
            <a href='/admin-dashboard.php?token=<?php echo $validToken; ?>' 
               class='px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors'>
                ← Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
