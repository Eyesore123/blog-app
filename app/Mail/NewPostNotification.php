<?php

namespace App\Mail;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use League\CommonMark\CommonMarkConverter;
use Illuminate\Support\Facades\URL;

class NewPostNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function build()
    {
        $converter = new CommonMarkConverter([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);
        $htmlContent = $converter->convert($this->post->content);

        $imageUrl = $this->post->image_url;
        if ($imageUrl && !preg_match('/^https?:\/\//', $imageUrl)) {
            $imageUrl = asset($imageUrl);
        }

        $imageHtml = $imageUrl
            ? "<img src=\"{$imageUrl}\" alt=\"Post image\" style=\"max-width:100%;margin-bottom:1rem;\" />"
            : "";

        $unsubscribeUrl = URL::temporarySignedRoute(
            'unsubscribe', now()->addDays(7), [
                'email' => $this->to['email'],
                'type' => 'post'
            ]
        );

        $emailHtml = "
            <html>
                <body>
                    <h2>Here's the latest blog post from Joni&#39;s blog:</h2>
                    <h1>{$this->post->title}</h1>
                    {$imageHtml}
                    <div style='font-size:16px;line-height:1.6;max-width:700px;margin-bottom:2rem;'>
                        {$htmlContent}
                    </div>
                    <a href='" . url('/posts/' . $this->post->slug) . "' style='color:#5800FF;'>Read more</a>
                    <p style='margin-top:2rem;'>
                        <a href='{$unsubscribeUrl}' style='color:#888;font-size:13px;'>Unsubscribe from these emails</a>
                    </p>
                </body>
            </html>
        ";

        return $this->subject("Joni's Blog: " . $this->post->title)
                    ->html($emailHtml);
    }
}