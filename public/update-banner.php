<?php
// Simple token check
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit;
}

// Connect to Postgres
$databaseUrl = getenv('DATABASE_URL');

if (!$databaseUrl) {
    die("DATABASE_URL is not set!");
}

// Parse DATABASE_URL
$dbParts = parse_url($databaseUrl);
$host = $dbParts['host'] ?? '';
$port = $dbParts['port'] ?? 5432;
$database = ltrim($dbParts['path'] ?? '', '/');
$username = $dbParts['user'] ?? '';
$password = $dbParts['pass'] ?? '';

$conn = pg_connect("host=$host port=$port dbname=$database user=$username password=$password");
if (!$conn) {
    die("Connection failed: " . pg_last_error());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = $_POST['message'] ?? '';
    $is_visible = isset($_POST['is_visible']) ? 1 : 0;

    // Escape message to prevent SQL injection
    $messageEscaped = pg_escape_string($conn, $message);

    // Update or insert if no row exists
    $result = pg_query($conn, "SELECT id FROM info_banners LIMIT 1");
    if (pg_num_rows($result) > 0) {
        pg_query($conn, "UPDATE info_banners SET message='$messageEscaped', is_visible=$is_visible, updated_at=NOW() WHERE id = 1");
    } else {
        pg_query($conn, "INSERT INTO info_banners (message, is_visible, created_at, updated_at) VALUES ('$messageEscaped', $is_visible, NOW(), NOW())");
    }

    header("Location: ".$_SERVER['PHP_SELF']."?token=".$providedToken);
    exit;
}

// Fetch current banner
$result = pg_query($conn, "SELECT * FROM info_banners LIMIT 1");
$banner = pg_fetch_assoc($result);
$currentMessage = $banner['message'] ?? '';
$isVisible = isset($banner['is_visible']) && $banner['is_visible'] == '1';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Info Banner</title>
<style>
    body { font-family: sans-serif; margin: 2rem; background: #f0f0f0; }
    .container { max-width: 600px; margin: auto; padding: 1rem; background: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);}
    textarea { width: 100%; padding: 0.5rem; font-size: 1rem; }
    input[type=checkbox] { transform: scale(1.2); margin-right: 0.5rem; }
    button { padding: 0.5rem 1rem; font-size: 1rem; background: #0074D9; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
    button:hover { background: #005bb5; }
</style>
</head>
<body>
<div class="container">
    <h2>Info Banner Settings</h2>
    <form method="post">
        <label for="message">Banner Message:</label><br>
        <textarea id="message" name="message" rows="4"><?= htmlspecialchars($currentMessage) ?></textarea><br><br>
        
        <label>
            <input type="checkbox" name="is_visible" <?= $isVisible ? 'checked' : '' ?>>
            Banner Visible
        </label><br><br>

        <button type="submit">Update Banner</button>
    </form>

    <hr>
    <h3>Current Status</h3>
    <p><strong>Visible:</strong> <?= $isVisible ? 'Yes' : 'No' ?></p>
    <p><strong>Message:</strong> <?= htmlspecialchars($currentMessage) ?></p>
</div>
</body>
</html>
