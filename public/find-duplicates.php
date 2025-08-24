<?php
// --- Auth ---
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

$action = $_GET['action'] ?? 'check';

echo "<h1>Duplicate ID Tool</h1>";

// --- DB Connection ---
$databaseUrl = getenv('DATABASE_URL');
if (!$databaseUrl) {
    echo "<p>DATABASE_URL is not set!</p>";
    exit;
}

$dbParts = parse_url($databaseUrl);
$host     = $dbParts['host'] ?? '';
$port     = $dbParts['port'] ?? 5432;
$database = ltrim($dbParts['path'] ?? '', '/');
$username = $dbParts['user'] ?? '';
$password = $dbParts['pass'] ?? '';

$dsn = "pgsql:host=$host;port=$port;dbname=$database;user=$username;password=$password";
try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "<p>Connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// --- Step 1: Find duplicates ---
$sql = "
    SELECT id, COUNT(*) as cnt
    FROM posts
    GROUP BY id
    HAVING COUNT(*) > 1
    ORDER BY cnt DESC
";
$duplicates = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

if (empty($duplicates) && $action !== 'repair') {
    echo "<p>No duplicate IDs found 🎉</p>";
    echo "<p><a href='?token=$validToken&action=repair' style='color:green;font-weight:bold;'>🛠 Repair Schema (Sequence + PK)</a></p>";
    exit;
}

if (!empty($duplicates)) {
    echo "<h2>Found Duplicates</h2>";
    echo "<table border='1' cellpadding='6'><tr><th>ID</th><th>Count</th></tr>";
    foreach ($duplicates as $dup) {
        echo "<tr><td>{$dup['id']}</td><td>{$dup['cnt']}</td></tr>";
    }
    echo "</table>";
}

// --- Action Buttons ---
echo "<p>";
echo "<a href='?token=$validToken&action=check'>🔍 Check Again</a> | ";
echo "<a href='?token=$validToken&action=fix' style='color:red;font-weight:bold;'>⚡ Fix Duplicates</a> | ";
echo "<a href='?token=$validToken&action=repair' style='color:green;font-weight:bold;'>🛠 Repair Schema</a>";
echo "</p>";

if ($action === 'fix') {
    echo "<h2>Fixing duplicates...</h2><ul>";

    // Get the sequence name automatically
    $seqQuery = "SELECT pg_get_serial_sequence('posts', 'id') as seqname";
    $seqName = $pdo->query($seqQuery)->fetchColumn();

    if (!$seqName) {
        echo "<li style='color:red;'>Could not detect sequence for posts.id!</li>";
        exit;
    }

    foreach ($duplicates as $dup) {
        $id = $dup['id'];

        // fetch rows with duplicate ID
        $stmt = $pdo->prepare("SELECT ctid, id FROM posts WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // skip the first, update the rest
        $skip = true;
        foreach ($rows as $row) {
            if ($skip) { $skip = false; continue; }

            // assign a fresh id from the sequence
            $newId = $pdo->query("SELECT nextval('$seqName')")->fetchColumn();

            $update = $pdo->prepare("UPDATE posts SET id = :newId WHERE ctid = :ctid");
            $update->execute([':newId' => $newId, ':ctid' => $row['ctid']]);

            echo "<li>Updated duplicate ID {$row['id']} → $newId</li>";
        }
    }

    echo "</ul><p>All duplicates fixed ✅</p>";

    // --- Step 3: Reseed sequence ---
    $pdo->exec("SELECT setval('$seqName', (SELECT MAX(id) FROM posts)+1)");

    echo "<p>Sequence <code>$seqName</code> reset to max(id)+1</p>";
    echo "<p><a href='?token=$validToken&action=repair'>🛠 Now Repair Schema</a></p>";
}

if ($action === 'repair') {
    echo "<h2>Repairing Schema...</h2><ul>";

    // Step 1: Ensure sequence exists
    $pdo->exec("CREATE SEQUENCE IF NOT EXISTS posts_id_seq");
    echo "<li>Ensured sequence <code>posts_id_seq</code> exists</li>";

    // Step 2: Attach sequence to posts.id
    $pdo->exec("ALTER TABLE posts ALTER COLUMN id SET DEFAULT nextval('posts_id_seq')");
    echo "<li>Attached sequence to <code>posts.id</code></li>";

    // Step 3: Drop and recreate PK
    $pdo->exec("ALTER TABLE posts DROP CONSTRAINT IF EXISTS posts_pkey");
    $pdo->exec("ALTER TABLE posts ADD CONSTRAINT posts_pkey PRIMARY KEY (id)");
    echo "<li>Primary Key enforced on <code>posts.id</code></li>";

    // Step 4: Reset sequence
    $pdo->exec("SELECT setval('posts_id_seq', (SELECT MAX(id) FROM posts)+1)");
    echo "<li>Sequence reseeded to MAX(id)+1</li>";

    echo "</ul><p>Schema repair complete ✅</p>";
    echo "<p><a href='?token=$validToken&action=check'>🔍 Re-check</a></p>";
}
