<?php

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
use Illuminate\Support\Facades\Log;

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
                // Queue the email safely
                Mail::to($myEmail)->queue(new NewPostNotification($post, $myEmail));

                Log::info("Test email queued", [
                    'email' => $myEmail,
                    'post_id' => $post->id,
                ]);

                $message = "Test email queued successfully for {$myEmail} (Post ID: {$post->id})";
            } catch (\Throwable $e) {
                Log::error("Failed to queue test email", [
                    'email' => $myEmail,
                    'post_id' => $post->id ?? null,
                    'error' => $e->getMessage()
                ]);

                $message = "Failed to queue email: " . htmlspecialchars($e->getMessage());
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
body { font-family: sans-serif; margin: 2rem; background: #f9f9f9; }
.container { max-width: 500px; margin: auto; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
input[type="email"] { padding: 0.5rem; width: 100%; margin-top: 0.5rem; }
button { padding: 0.5rem 1rem; margin-top: 1rem; cursor: pointer; }
.message { margin: 1rem 0; padding: 0.5rem; background: #e0ffe0; border: 1px solid #8fc98f; border-radius: 4px; }
h1 { font-size: 1.5rem; margin-bottom: 1rem; text-align: center; }
</style>
</head>
<body>
<div class="container">
<h1>Send Latest Post Test Email</h1>

<?php if ($message) echo "<div class='message'>{$message}</div>"; ?>

<form method="POST">
    <label for="email">Email address:</label>
    <input type="email" name="email" id="email" required placeholder="you@example.com">
    <button type="submit">Send Test Email</button>
</form>
</div>
</body>
</html>
