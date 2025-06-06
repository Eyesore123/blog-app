<?php
namespace App\Mail;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use League\CommonMark\CommonMarkConverter;

class NewPostNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $post;

    /**
     * Create a new message instance.
     *
     * @param Post $post
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * Build the message.
     *
     * @return $this
     */

    public function build()
    {
        $converter = new CommonMarkConverter([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);
        $htmlContent = $converter->convert($this->post->content);

        $emailHtml = "
            <html>
                <body>
                    <h2>Here's the latest blog post from Joni&#39;s blog:</h2>
                    <h1>{$this->post->title}</h1>
                    <div style='font-size:16px;line-height:1.6;max-width:700px;margin-bottom:2rem;'>
                        {$htmlContent}
                    </div>
                    <a href='" . url('/posts/' . $this->post->slug) . "' style='color:#5800FF;'>Read more</a>
                </body>
            </html>
        ";

        return $this->subject('Joni&#39;s Blog: ' . $this->post->title)
                    ->html($emailHtml);
    }
}