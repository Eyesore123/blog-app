<?php
// send_latest_post_test.php

$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Post;
use App\Mail\NewPostNotification;
use Illuminate\Support\Facades\Mail;

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $myEmail = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);

    if (!$myEmail) {
        $message = "Please enter a valid email address.";
    } else {
        $post = Post::latest()->first();

        if (!$post) {
            $message = "No posts found to send.";
        } else {
            try {
                Mail::to($myEmail)->queue(new NewPostNotification($post->id, $myEmail));
                $message = "Test email queued for {$myEmail} (Post ID: {$post->id})";
            } catch (\Throwable $e) {
                $message = "Failed to queue email: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Send Latest Post Test Email</title>
<style>
body { font-family: sans-serif; margin: 2rem; }
input[type="email"] { padding: 0.5rem; width: 300px; }
button { padding: 0.5rem 1rem; margin-left: 0.5rem; }
.message { margin: 1rem 0; color: green; }
</style>
</head>
<body>
<h1>Send Latest Post Test Email</h1>

<?php if ($message) echo "<p class='message'>{$message}</p>"; ?>

<form method="POST">
    <label for="email">Email address:</label>
    <input type="email" name="email" id="email" required placeholder="you@example.com">
    <button type="submit">Send Test Email</button>
</form>

</body>
</html>
