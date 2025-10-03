<?php
$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $jobId  = $_POST['job_id'] ?? null;

    if ($action === 'flush_pending' && DB::getSchemaBuilder()->hasTable('jobs')) {
        DB::table('jobs')->truncate();
        $message = "Pending queue flushed successfully!";
    } elseif ($action === 'flush_failed' && DB::getSchemaBuilder()->hasTable('failed_jobs')) {
        DB::table('failed_jobs')->truncate();
        $message = "Failed jobs flushed successfully!";
    } elseif ($action === 'flush_broken') {
        // Remove jobs that fail unserialization
        $broken = 0;
        foreach (DB::table('jobs')->get() as $job) {
            $payload = json_decode($job->payload, true);
            if (!isset($payload['data']['command']) || @unserialize(base64_decode($payload['data']['command']), ['allowed_classes' => true]) === false) {
                DB::table('jobs')->where('id', $job->id)->delete();
                $broken++;
            }
        }
        $message = "Flushed {$broken} broken pending job(s).";
    } elseif ($action === 'delete_job' && $jobId) {
        DB::table('jobs')->where('id', $jobId)->delete();
        DB::table('failed_jobs')->where('id', $jobId)->delete();
        $message = "Job #{$jobId} deleted!";
    } elseif ($action === 'retry_failed' && $jobId) {
        $job = DB::table('failed_jobs')->where('id', $jobId)->first();
        if ($job && isset($job->payload)) {
            Queue::pushRaw($job->payload, $job->queue ?? 'default');
            DB::table('failed_jobs')->where('id', $jobId)->delete();
            $message = "Failed job #{$jobId} re-queued successfully!";
        }
    } elseif ($action === 'retry_pending' && $jobId) {
        $job = DB::table('jobs')->where('id', $jobId)->first();
        if ($job && isset($job->payload)) {
            Queue::pushRaw($job->payload, $job->queue ?? 'default');
            DB::table('jobs')->where('id', $jobId)->delete();
            $message = "Pending job #{$jobId} retried successfully!";
        }
    }
}

// Fetch jobs
$pendingJobs = DB::getSchemaBuilder()->hasTable('jobs') ? DB::table('jobs')->get() : [];
$failedJobs  = DB::getSchemaBuilder()->hasTable('failed_jobs') ? DB::table('failed_jobs')->get() : [];

// Safe job display function
function prettyJob($job) {
    $payload = isset($job->payload) ? json_decode($job->payload, true) : null;
    $constructorParams = [];
    $jobClass = '(unknown)';

    if ($payload) {
        if (!empty($payload['displayName'])) {
            $jobClass = $payload['displayName'];
        } elseif (!empty($payload['data']['commandName'])) {
            $jobClass = $payload['data']['commandName'];
        } elseif (!empty($payload['data']['command'])) {
            $jobClass = '(serialized command)';
        }

        if (!empty($payload['data']['command'])) {
            $commandData = $payload['data']['command'];
            try {
                $decoded = base64_decode($commandData);
                $command = @unserialize($decoded, ['allowed_classes' => true]);
                if (is_object($command)) {
                    $constructorParams = get_object_vars($command);
                } else {
                    $constructorParams = ['raw_payload' => $commandData];
                }
            } catch (\Throwable $e) {
                $constructorParams = ['error' => $e->getMessage(), 'raw_payload' => $commandData];
            }
        }
    }

    return [
        'id'          => $job->id ?? null,
        'queue'       => $job->queue ?? null,
        'class'       => $jobClass,
        'attempts'    => $job->attempts ?? 0,
        'payload'     => $constructorParams ?: $payload,
        'failed_at'   => $job->failed_at ?? null,
        'reserved_at' => $job->reserved_at ?? null,
        'available_at'=> $job->available_at ?? null,
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Queue Dashboard</title>
<style>
body { font-family: sans-serif; margin: 2rem; }
button { margin: 0.5rem; padding: 0.5rem 1rem; cursor: pointer; }
pre { background: #f4f4f4; padding: 1rem; max-height: 400px; overflow: auto; }
h2 { margin-top: 2rem; }
.message { color: green; margin-bottom: 1rem; }
table { border-collapse: collapse; width: 100%; margin-bottom: 2rem; }
th, td { border: 1px solid #ccc; padding: 0.5rem; text-align: left; vertical-align: top; }
th { background: #eee; }
form.inline { display:inline; }
</style>
</head>
<body>
<h1>Queue Dashboard</h1>

<?php if (!empty($message)) echo "<p class='message'>{$message}</p>"; ?>

<h2>Pending Jobs (<?= count($pendingJobs) ?>)</h2>
<form method="POST">
    <input type="hidden" name="action" value="flush_pending">
    <button type="submit">Flush Pending Queue</button>
    <input type="hidden" name="action" value="flush_broken">
    <button type="submit" name="action" value="flush_broken">Flush Broken Jobs</button>
</form>
<table>
<tr><th>ID</th><th>Queue</th><th>Class</th><th>Attempts</th><th>Payload</th><th>Actions</th></tr>
<?php foreach ($pendingJobs as $job): 
    $j = prettyJob($job); ?>
<tr>
    <td><?= $j['id'] ?></td>
    <td><?= htmlspecialchars($j['queue']) ?></td>
    <td><?= htmlspecialchars($j['class']) ?></td>
    <td><?= $j['attempts'] ?></td>
    <td><pre><?= htmlspecialchars(json_encode($j['payload'], JSON_PRETTY_PRINT)) ?></pre></td>
    <td>
        <form method="POST" class="inline">
            <input type="hidden" name="action" value="delete_job">
            <input type="hidden" name="job_id" value="<?= $j['id'] ?>">
            <button type="submit">Delete</button>
        </form>
        <form method="POST" class="inline">
            <input type="hidden" name="action" value="retry_pending">
            <input type="hidden" name="job_id" value="<?= $j['id'] ?>">
            <button type="submit">Retry</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>

<h2>Failed Jobs (<?= count($failedJobs) ?>)</h2>
<form method="POST">
    <input type="hidden" name="action" value="flush_failed">
    <button type="submit">Flush Failed Jobs</button>
</form>
<table>
<tr><th>ID</th><th>Queue</th><th>Class</th><th>Attempts</th><th>Payload</th><th>Failed At</th><th>Actions</th></tr>
<?php foreach ($failedJobs as $job): 
    $j = prettyJob($job); ?>
<tr>
    <td><?= $j['id'] ?></td>
    <td><?= htmlspecialchars($j['queue']) ?></td>
    <td><?= htmlspecialchars($j['class']) ?></td>
    <td><?= $j['attempts'] ?></td>
    <td><pre><?= htmlspecialchars(json_encode($j['payload'], JSON_PRETTY_PRINT)) ?></pre></td>
    <td><?= $j['failed_at'] ?></td>
    <td>
        <form method="POST" class="inline">
            <input type="hidden" name="action" value="retry_failed">
            <input type="hidden" name="job_id" value="<?= $j['id'] ?>">
            <button type="submit">Retry</button>
        </form>
        <form method="POST" class="inline">
            <input type="hidden" name="action" value="delete_job">
            <input type="hidden" name="job_id" value="<?= $j['id'] ?>">
            <button type="submit">Delete</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>

</body>
</html>
