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
    $mode = ($_POST['mode'] ?? 'queue') === 'send' ? 'send' : 'queue';

    if (! $myEmail) {
        $message = "Please enter a valid email address.";
    } else {
        $post = Post::latest()->first();
        if (! $post) {
            $message = "No posts found to send.";
        } else {
            try {
                if ($mode === 'send') {
                    // synchronous send (useful for testing)
                    Mail::to($myEmail)->send(new NewPostNotification($post->id, $myEmail));
                    Log::info("Test email SENT (sync)", ['email' => $myEmail, 'post_id' => $post->id]);
                    $message = "Test email SENT successfully to {$myEmail} (Post ID: {$post->id}).";
                } else {
                    // queue the job for the worker
                    Mail::to($myEmail)->queue(new NewPostNotification($post->id, $myEmail));
                    Log::info("Test email QUEUED", ['email' => $myEmail, 'post_id' => $post->id]);
                    $message = "Test email queued for {$myEmail} (Post ID: {$post->id}).";
                }
            } catch (\Throwable $e) {
                Log::error("Failed to queue/send test email", [
                    'email' => $myEmail,
                    'post_id' => $post->id,
                    'error' => $e->getMessage()
                ]);
                $message = "Failed to queue/send email: " . htmlspecialchars($e->getMessage());
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Send Latest Post Test Email</title>
<style>
body{font-family:sans-serif;margin:2rem;background:#f6f7fb}
.container{max-width:520px;margin:auto;background:#fff;padding:1.5rem;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,.06)}
input[type=email]{width:100%;padding:.6rem;margin-top:.4rem}
button{padding:.6rem 1rem;margin-top:.8rem}
.message{background:#e8f9e8;border:1px solid #9fd59f;padding:.6rem;border-radius:4px;margin-bottom:1rem}
fieldset{margin-top:.6rem}
</style>
</head>
<body>
<div class="container">
<h1>Send Latest Post Test Email</h1>

<?php if (!empty($message)) echo "<div class='message'>{$message}</div>"; ?>

<form method="POST">
  <label for="email">Recipient email</label>
  <input id="email" name="email" type="email" required placeholder="you@example.com">

  <fieldset>
    <legend>Mode</legend>
    <label><input type="radio" name="mode" value="queue" checked> Queue (recommended)</label><br>
    <label><input type="radio" name="mode" value="send"> Send now (sync)</label>
  </fieldset>

  <button type="submit">Send Test Email</button>
</form>
<p style="font-size:12px;color:#666;margin-top:1rem">Make sure you open your logs to confirm and that your queue worker is running if you selected "Queue".</p>
</div>
</body>
</html>
