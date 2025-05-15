<?php
namespace App\Mail;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

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
        $htmlContent = "
            <html>
                <body>
                    <h1>{$this->post->title}</h1>
                    <p>{$this->post->content}</p>
                    <a href='" . url('/posts/' . $this->post->slug) . "'>Read more</a>
                </body>
            </html>
        ";

        return $this->subject('New Post: ' . $this->post->title)
                    ->html($htmlContent);
    }
}