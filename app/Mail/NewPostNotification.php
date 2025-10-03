<?php

namespace App\Mail;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use League\CommonMark\CommonMarkConverter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewPostNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $postId;
    public $email;
    public $post;

    public function __construct(int $postId, string $email)
    {
        $this->postId = $postId;
        $this->email  = $email;
    }

    public function build()
    {
        // Load the post safely
        if (!isset($this->post)) {
            $this->post = Post::find($this->postId);

            if (!$this->post) {
                Log::error("NewPostNotification: Post not found", ['post_id' => $this->postId]);
                throw new \Exception("Post not found (ID: {$this->postId})");
            }
        }

        Log::info("Building email HTML for Post ID: {$this->post->id}");

        // Convert Markdown content to HTML
        $converter   = new CommonMarkConverter([
            'html_input'         => 'escape',
            'allow_unsafe_links' => false,
        ]);
        $htmlContent = $converter->convert($this->post->content);

        // Handle post image
        $imageUrl = $this->post->image_path;
        if ($imageUrl && !preg_match('/^https?:\/\//', $imageUrl)) {
            $imageUrl = asset('storage/' . str_replace('\\', '', $imageUrl));
        }
        $imageHtml = $imageUrl
            ? "<img src=\"{$imageUrl}\" alt=\"Post image\" style=\"max-width:500px;margin-bottom:1rem;\" />"
            : "";

        // Generate temporary unsubscribe URL
        $unsubscribeUrl = URL::temporarySignedRoute(
            'unsubscribe',
            now()->addDays(7),
            ['email' => $this->email, 'type' => 'post']
        );

        // Build full email HTML
        $emailHtml = "
            <html>
                <body style='text-align:center;'>
                    <div style='max-width:700px;margin:auto;padding:20px;'>
                        <h2>Here's the latest blog post from Joni's blog:</h2>
                        <h1>{$this->post->title}</h1>
                        {$imageHtml}
                        <div style='font-size:16px;line-height:1.6;max-width:700px;margin-bottom:2rem;text-align:left;'>
                            {$htmlContent}
                        </div>
                        <a href='" . url('/posts/' . $this->post->slug) . "' style='color:#5800FF;'>Read more</a>
                        <p style='margin-top:2rem;'>
                            <a href='{$unsubscribeUrl}' style='color:#888;font-size:13px;'>Unsubscribe from these emails</a>
                        </p>
                    </div>
                </body>
            </html>
        ";

        return $this->subject("Joni's Blog: " . $this->post->title)
                    ->html($emailHtml);
    }
}
