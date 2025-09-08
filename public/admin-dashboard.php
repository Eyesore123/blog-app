<?php
// Simple security check
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    http_response_code(404);
    echo "Not Found";
    exit;
}

// Auto-discover PHP files in public directory
$publicDir = __DIR__;
$allPhpFiles = glob($publicDir . '/*.php');
$discoveredFiles = [];

foreach ($allPhpFiles as $file) {
    $filename = basename($file);
    $discoveredFiles[] = $filename;
}

// Comprehensive categorization of all your scripts
$categories = [
    'Database Backup & Management' => [
        'backup-database.php' => '📦 Create a new PostgreSQL database backup',
        'manage-backups.php' => '📋 View, download, restore, and delete backup files', 
        'download-backup.php' => '⬇️ Download backup files directly to your computer'
    ],
    'Database Connection & Testing' => [
        'check-connection.php' => '🔌 Test database connection status',
        'db-test.php' => '🧪 Run comprehensive database tests',
        'pg-test.php' => '🐘 PostgreSQL specific connection tests',
        'db-direct.php' => '🔗 Direct database connection testing',
        'force-pgsql.php' => '🔧 Force PostgreSQL as default database driver'
    ],
    'Database Schema & Tables' => [
        'check-schema.php' => '📊 Check database schema structure',
        'check-tables.php' => '📋 List and verify database tables',
        'create-tables.php' => '🏗️ Create missing database tables',
        'create-tags-tables.php' => '🏷️ Create tags and post_tag tables if missing',
        'create-sketches-table.php' => '🎨 Create sketches table if missing',
        'pg-create-tables.php' => '🐘 Create PostgreSQL specific tables',
        'create-cache-table.php' => '💾 Create cache table for sessions',
        'setup-db.php' => '⚙️ Complete database setup and initialization'
    ],
    'User Management' => [
        'create-admin.php' => '👑 Create a new admin user account',
        'pg-create-admin.php' => '🐘 Create admin user in PostgreSQL',
        'fix-user-model.php' => '🔧 Fix User model configuration and relationships',
        'fix-users-table.php' => '👥 Fix users table structure and constraints'
    ],
    'Configuration Management' => [
        'check-config.php' => '⚙️ Check Laravel configuration files',
        'db-config-fix.php' => '🔧 Fix database configuration issues',
        'direct-config.php' => '📝 Direct configuration file editing',
        'update-config.php' => '🔄 Update application configuration',
        'update-cache-config.php' => '💾 Update cache configuration settings'
    ],
    'Environment & Railway Setup' => [
        'railway-env.php' => '🚂 Check and configure Railway environment variables',
        'railway-cache-env.php' => '💾 Configure Railway cache environment',
        'update-env.php' => '🔄 Update environment variables',
        'check-code.php' => '🔍 Check code configuration and setup'
    ],
    'Cache Management' => [
        'direct-cache-fix.php' => '💾 Direct cache configuration fixes',
        'create-storage.php' => '📁 Create storage directories and permissions'
    ],
    'Admin Panel & Controllers' => [
        'fix-admin-controller.php' => '🎛️ Fix AdminController functionality and routes',
        'fix-admin-view.php' => '🎨 Fix admin view templates and layouts'
    ],
    'Migration & Database Fixes' => [
        'fix-migration.php' => '🔄 Fix database migration issues',
        'db-info.php' => 'ℹ️ Display detailed database information'
    ],
    'Deployment & Maintenance' => [
        'post-deploy.php' => '🚀 Post-deployment setup and cache clearing',
        'run-all-fixes.php' => '🔧 Run multiple fixes in sequence automatically'
    ],
    'Development Tools' => [
        'scan-public-files.php' => '🔍 Scan and list all files in public directory',
        'phpinfo.php' => '🐘 Display PHP configuration and environment info',
        'admin-dashboard.php' => '🎛️ This comprehensive admin dashboard'
    ],
    'Core Application' => [
        'index.php' => '🏠 Main application entry point (Laravel public/index.php)'
    ],
    'Post Fixes & Validations' => [
        'check-recent-uploads.php' => '🆕 Check for recently uploaded files',
        'complete-post-fix.php' => 'Complete all post fixes and validations',
        'debug-post-creation.php' => '🐞 Debug post creation issues',
        'fix-all-post-issues.php' => '🔧 Fix all known post-related issues',
        'find-duplicates.php' => '🔍 Find and manage duplicate id:s',
        'edit-tags.php' => '🏷️ Edit and manage post tags directly',
        'data-updates.php' => 'Change post update dates in bulk'
    ],
    'Storage Management' => [
        'checkstorage.php' => '📁 Check storage directories',
        'degug-image-access.php' => '🐞 Debug image access issues',
        'image-browser.php' => '🖼️ Browse and manage uploaded images'
    ]
];

// Count files by status
$existingFiles = 0;
$missingFiles = 0;
foreach ($categories as $scripts) {
    foreach ($scripts as $script => $description) {
        if (file_exists($publicDir . '/' . $script)) {
            $existingFiles++;
        } else {
            $missingFiles++;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔧 Admin Dashboard - Blog App</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .admin-card {
            transition: all 0.3s ease;
        }
        .admin-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .category-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .quick-action {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .file-exists { border-left: 4px solid #10b981; }
        .file-missing { border-left: 4px solid #ef4444; }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-100 to-gray-200 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-white shadow-xl rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold text-gray-800 mb-2">🔧 Admin Dashboard</h1>
                    <p class="text-gray-600">Comprehensive administrative tools for Blog App</p>
                    <p class="text-sm text-gray-500 mt-1">Railway PostgreSQL Environment</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">Server Time</div>
                    <div class="text-lg font-semibold"><?php echo date('Y-m-d H:i:s'); ?></div>
                    <div class="text-xs text-gray-400 mt-1">UTC</div>
                </div>
            </div>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="/" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">← Back to Site</a>
                <button onclick="toggleAllCategories()" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">Toggle All</button>
                <button onclick="refreshPage()" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">🔄 Refresh</button>
                <a href="/phpinfo.php?token=<?php echo $validToken; ?>" target="_blank" class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors">🐘 PHP Info</a>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="stats-card text-white p-6 rounded-lg">
                <div class="text-2xl font-bold"><?php echo $existingFiles; ?></div>
                <div class="text-sm opacity-90">Files Available</div>
            </div>
            <div class="bg-red-500 text-white p-6 rounded-lg">
                <div class="text-2xl font-bold"><?php echo $missingFiles; ?></div>
                <div class="text-sm opacity-90">Files Missing</div>
            </div>
            <div class="bg-green-500 text-white p-6 rounded-lg">
                <div class="text-2xl font-bold"><?php echo count($categories); ?></div>
                <div class="text-sm opacity-90">Categories</div>
            </div>
            <div class="bg-yellow-500 text-white p-6 rounded-lg">
                <div class="text-2xl font-bold"><?php echo count($discoveredFiles); ?></div>
                <div class="text-sm opacity-90">Total PHP Files</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white shadow-lg rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-semibold mb-4 text-gray-800">🚀 Most Used Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="/backup-database.php?token=<?php echo $validToken; ?>" 
                   class="quick-action p-4 text-white rounded-lg text-center hover:opacity-90 transition-opacity">
                    <div class="text-2xl mb-2">📦</div>
                    <div class="font-semibold">Create Backup</div>
                </a>
                <a href="/manage-backups.php?token=<?php echo $validToken; ?>" 
                   class="p-4 bg-gradient-to-r from-green-400 to-blue-500 text-white rounded-lg text-center hover:opacity-90 transition-opacity">
                    <div class="text-2xl mb-2">📋</div>
                    <div class="font-semibold">Manage Backups</div>
                </a>
                <a href="/db-test.php?token=<?php echo $validToken; ?>" 
                   class="p-4 bg-gradient-to-r from-yellow-400 to-orange-500 text-white rounded-lg text-center hover:opacity-90 transition-opacity">
                    <div class="text-2xl mb-2">🧪</div>
                    <div class="font-semibold">Test Database</div>
                </a>
                <a href="/post-deploy.php?token=<?php echo $validToken; ?>" 
                   class="p-4 bg-gradient-to-r from-purple-400 to-pink-500 text-white rounded-lg text-center hover:opacity-90 transition-opacity">
                    <div class="text-2xl mb-2">🚀</div>
                    <div class="font-semibold">Post Deploy</div>
                </a>
            </div>
        </div>

        <!-- Categorized Scripts -->
        <?php foreach ($categories as $categoryName => $scripts): ?>
        <div class="mb-6">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="category-header text-white p-4 cursor-pointer" onclick="toggleCategory('<?php echo strtolower(str_replace([' ', '&'], ['-', ''], $categoryName)); ?>')">
                    <h2 class="text-xl font-semibold flex items-center justify-between">
                        <?php echo $categoryName; ?>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm opacity-75"><?php echo count($scripts); ?> files</span>
                            <span class="toggle-icon transition-transform">▼</span>
                        </div>
                    </h2>
                </div>
                
                <div id="<?php echo strtolower(str_replace([' ', '&'], ['-', ''], $categoryName)); ?>" class="category-content hidden">
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($scripts as $script => $description): ?>
                            <?php 
                            $fileExists = file_exists($publicDir . '/' . $script);
                            $cardClass = $fileExists ? 'file-exists' : 'file-missing';
                            $fileSize = $fileExists ? filesize($publicDir . '/' . $script) : 0;
                            $fileModified = $fileExists ? date('M j, H:i', filemtime($publicDir . '/' . $script)) : '';
                            ?>
                            <div class="admin-card <?php echo $cardClass; ?> bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <div class="flex items-start justify-between mb-2">
                                    <h3 class="font-semibold text-gray-800 text-sm"><?php echo $script; ?></h3>
                                    <?php if ($fileExists): ?>
                                        <span class="text-green-500 text-xs font-bold">✓</span>
                                    <?php else: ?>
                                        <span class="text-red-500 text-xs font-bold">✗</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm text-gray-600 mb-3"><?php echo $description; ?></p>
                                
                                <?php if ($fileExists): ?>
                                <div class="text-xs text-gray-500 mb-3">
                                    <div>Size: <?php echo number_format($fileSize); ?> bytes</div>
                                    <div>Modified: <?php echo $fileModified; ?></div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <a href="/<?php echo $script; ?>?token=<?php echo $validToken; ?>" 
                                       class="px-3 py-1 bg-green-500 text-white text-xs rounded hover:bg-green-600 transition-colors"
                                       target="_blank">
                                        ▶ Run
                                    </a>
                                    <button onclick="viewSource('<?php echo $script; ?>')" 
                                            class="px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 transition-colors">
                                        📄 Code
                                    </button>
                                                                        <button onclick="getFileInfo('<?php echo $script; ?>')" 
                                            class="px-3 py-1 bg-gray-500 text-white text-xs rounded hover:bg-gray-600 transition-colors">
                                        ℹ Info
                                    </button>
                                </div>
                                <?php else: ?>
                                <div class="text-xs text-red-600 bg-red-50 p-2 rounded">File not found in public directory</div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Undocumented Files -->
        <?php
        $documentedFiles = [];
        foreach ($categories as $scripts) {
            $documentedFiles = array_merge($documentedFiles, array_keys($scripts));
        }
        $undocumentedFiles = array_diff($discoveredFiles, $documentedFiles);
        ?>
        
        <?php if (!empty($undocumentedFiles)): ?>
        <div class="mb-6">
            <div class="bg-yellow-50 border border-yellow-200 shadow-lg rounded-lg overflow-hidden">
                <div class="bg-yellow-500 text-white p-4 cursor-pointer" onclick="toggleCategory('undocumented-files')">
                    <h2 class="text-xl font-semibold flex items-center justify-between">
                        ⚠️ Undocumented Files
                        <div class="flex items-center space-x-2">
                            <span class="text-sm opacity-75"><?php echo count($undocumentedFiles); ?> files</span>
                            <span class="toggle-icon transition-transform">▼</span>
                        </div>
                    </h2>
                </div>
                
                <div id="undocumented-files" class="category-content hidden">
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($undocumentedFiles as $file): ?>
                            <?php 
                            $fileSize = filesize($publicDir . '/' . $file);
                            $fileModified = date('M j, H:i', filemtime($publicDir . '/' . $file));
                            ?>
                            <div class="admin-card bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <div class="flex items-start justify-between mb-2">
                                    <h3 class="font-semibold text-gray-800 text-sm"><?php echo $file; ?></h3>
                                    <span class="text-yellow-500 text-xs font-bold">?</span>
                                </div>
                                <p class="text-sm text-gray-600 mb-3">Undocumented file - needs categorization</p>
                                <div class="text-xs text-gray-500 mb-3">
                                    <div>Size: <?php echo number_format($fileSize); ?> bytes</div>
                                    <div>Modified: <?php echo $fileModified; ?></div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <a href="/<?php echo $file; ?>?token=<?php echo $validToken; ?>" 
                                       class="px-3 py-1 bg-yellow-500 text-white text-xs rounded hover:bg-yellow-600 transition-colors"
                                       target="_blank">
                                        ▶ Run
                                    </a>
                                    <button onclick="viewSource('<?php echo $file; ?>')" 
                                            class="px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 transition-colors">
                                        📄 Code
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="bg-white shadow-lg rounded-lg p-6 text-center">
            <p class="text-gray-600">🔒 Secure Admin Dashboard - Blog App Production Environment</p>
            <p class="text-sm text-gray-500 mt-2">Last updated: <?php echo date('Y-m-d H:i:s'); ?> UTC</p>
        </div>
    </div>

    <!-- Source Code Modal -->
    <div id="sourceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-6xl w-full max-h-screen overflow-hidden">
                <div class="flex justify-between items-center p-4 border-b bg-gray-50">
                    <h3 id="modalTitle" class="text-lg font-semibold">Source Code</h3>
                    <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 text-xl">✕</button>
                </div>
                <div class="p-4 overflow-auto" style="max-height: 70vh;">
                    <pre id="sourceCode" class="bg-gray-900 text-green-400 p-4 rounded text-sm overflow-auto font-mono"></pre>
                </div>
            </div>
        </div>
    </div>

    <!-- File Info Modal -->
    <div id="infoModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-2xl w-full">
                <div class="flex justify-between items-center p-4 border-b bg-gray-50">
                    <h3 id="infoModalTitle" class="text-lg font-semibold">File Information</h3>
                    <button onclick="closeInfoModal()" class="text-gray-500 hover:text-gray-700 text-xl">✕</button>
                </div>
                <div class="p-4">
                    <div id="fileInfo" class="space-y-3"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleCategory(categoryId) {
            const content = document.getElementById(categoryId);
            const icon = content.previousElementSibling.querySelector('.toggle-icon');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
            } else {
                content.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        }

        function toggleAllCategories() {
            const categories = document.querySelectorAll('.category-content');
            const icons = document.querySelectorAll('.toggle-icon');
            const allHidden = Array.from(categories).every(cat => cat.classList.contains('hidden'));
            
            categories.forEach((category, index) => {
                if (allHidden) {
                    category.classList.remove('hidden');
                    icons[index].style.transform = 'rotate(180deg)';
                } else {
                    category.classList.add('hidden');
                    icons[index].style.transform = 'rotate(0deg)';
                }
            });
        }

        function refreshPage() {
            window.location.reload();
        }

        function viewSource(filename) {
            fetch(`/view-source.php?file=${filename}&token=<?php echo $validToken; ?>`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('modalTitle').textContent = `Source Code: ${filename}`;
                    document.getElementById('sourceCode').textContent = data;
                    document.getElementById('sourceModal').classList.remove('hidden');
                })
                .catch(error => {
                    alert('Error loading source code: ' + error);
                });
        }

        function getFileInfo(filename) {
            fetch(`/file-info.php?file=${filename}&token=<?php echo $validToken; ?>`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('infoModalTitle').textContent = `File Info: ${filename}`;
                    const infoDiv = document.getElementById('fileInfo');
                    infoDiv.innerHTML = `
                        <div class="grid grid-cols-2 gap-4">
                            <div><strong>Filename:</strong> ${data.name}</div>
                            <div><strong>Size:</strong> ${data.size} bytes</div>
                            <div><strong>Modified:</strong> ${data.modified}</div>
                            <div><strong>Type:</strong> ${data.type}</div>
                        </div>
                        <div class="mt-4">
                            <strong>Full Path:</strong><br>
                            <code class="bg-gray-100 p-2 rounded block">${filename}</code>
                        </div>
                    `;
                    document.getElementById('infoModal').classList.remove('hidden');
                })
                .catch(error => {
                    alert('Error loading file info: ' + error);
                });
        }

        function closeModal() {
            document.getElementById('sourceModal').classList.add('hidden');
        }

        function closeInfoModal() {
            document.getElementById('infoModal').classList.add('hidden');
        }

        // Close modals when clicking outside
        document.getElementById('sourceModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        document.getElementById('infoModal').addEventListener('click', function(e) {
            if (e.target === this) closeInfoModal();
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
                closeInfoModal();
            }
        });
    </script>
</body>
</html>

