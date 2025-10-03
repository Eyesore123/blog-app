<?php

namespace App\Mail;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use League\CommonMark\CommonMarkConverter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewPostNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $postId;
    public string $email;

    /**
     * Only store primitive values (post ID + email) so the queued payload
     * stays small and avoids unserialize/model issues.
     */
    public function __construct(int $postId, string $email)
    {
        $this->postId = $postId;
        $this->email  = $email;
    }

    public function build()
    {
        // Rehydrate the Post when the job runs
        $post = Post::find($this->postId);

        if (! $post) {
            Log::error("NewPostNotification: post not found", [
                'post_id' => $this->postId,
                'email'   => $this->email,
            ]);

            return $this->subject("Post not found")
                        ->html("<p>Sorry — the post could not be found.</p>");
        }

        Log::info("NewPostNotification: building email for {$this->email} (post #{$post->id})");

        $converter = new CommonMarkConverter([
            'html_input'         => 'escape',
            'allow_unsafe_links' => false,
        ]);
        $htmlContent = $converter->convert($post->content);

        // Build a reliable absolute URL for the image
        $imageHtml = '';
        if ($post->image_path) {
            // Ensure forward slashes
            $publicPath = str_replace('\\', '/', $post->image_path);

            // Storage::disk('public')->url() usually returns "/storage/..."
            $maybeUrl = Storage::disk('public')->url($publicPath);

            // Make it absolute (url() will prepend APP_URL)
            $imageUrl = preg_match('#^https?://#', $maybeUrl) ? $maybeUrl : url($maybeUrl);

            $imageHtml = "<img src=\"{$imageUrl}\" alt=\"Post image\" style=\"max-width:500px;margin-bottom:1rem;\" />";
        }

        $unsubscribeUrl = URL::temporarySignedRoute(
            'unsubscribe',
            now()->addDays(7),
            [
                'email' => $this->email,
                'type'  => 'post',
            ]
        );

        $emailHtml = <<<HTML
<html>
  <body style="text-align:center;">
    <div style="max-width:700px;margin:auto;padding:20px;font-family:sans-serif;">
      <h2>Here's the latest blog post from Joni's Blog:</h2>
      <h1>{$post->title}</h1>
      {$imageHtml}
      <div style="font-size:16px;line-height:1.6;max-width:700px;margin-bottom:2rem;text-align:left;">
        {$htmlContent}
      </div>
      <a href="{$this->appUrl('/posts/' . $post->slug)}" style="color:#5800FF;">Read more</a>
      <p style="margin-top:2rem;font-size:13px;color:#888;">
        <a href="{$unsubscribeUrl}" style="color:#888;">Unsubscribe from these emails</a>
      </p>
    </div>
  </body>
</html>
HTML;

        return $this->subject("Joni's Blog: {$post->title}")
                    ->html($emailHtml);
    }

    /**
     * Small helper so we always build an absolute URL with APP_URL if needed.
     */
    protected function appUrl(string $path = ''): string
    {
        $path = ltrim($path, '/');
        $base = config('app.url') ? rtrim(config('app.url'), '/') : url('/');
        return $base . '/' . $path;
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("NewPostNotification failed for {$this->email}: {$exception->getMessage()}", [
            'trace' => $exception->getTraceAsString(),
            'post_id' => $this->postId,
        ]);
    }
}
