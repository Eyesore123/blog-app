<?php
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

// Build sitemap URL
$sitemapUrl = rtrim(
    (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'],
    '/'
) . '/sitemap.xml';

// Did user click the button?
$pinged = false;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pingUrl = 'https://www.google.com/ping?sitemap=' . urlencode($sitemapUrl);
    $response = @file_get_contents($pingUrl);
    $pinged = true;
    $success = ($response !== false);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Ping Google Sitemap</title>
<style>
body { font-family: sans-serif; padding: 2rem; background: #1f2937; color: #f9fafb; }
button {
  background: #10b981;
  color: white;
  padding: 12px 24px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}
button:hover { background: #059669; }
.result {
  margin-top: 1rem;
  padding: 1rem;
  border-radius: 6px;
}
.success { background: #065f46; }
.error { background: #7f1d1d; }
</style>
</head>
<body>
  <h1>📤 Ping Google with Sitemap</h1>
  <p>Sitemap URL: <code><?= htmlspecialchars($sitemapUrl) ?></code></p>

  <form method="POST">
    <button type="submit">Ping Google</button>
  </form>

  <?php if ($pinged): ?>
    <div class="result <?= $success ? 'success' : 'error' ?>">
      <?php if ($success): ?>
        ✅ Google Ping Successful! (<?= htmlspecialchars($sitemapUrl) ?>)
      <?php else: ?>
        ❌ Google Ping Failed. Check your network or sitemap URL.
      <?php endif; ?>
    </div>
  <?php endif; ?>
</body>
</html>
