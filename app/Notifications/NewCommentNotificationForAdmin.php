<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Comment;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewCommentNotificationForAdmin extends Notification implements ShouldQueue
{
    use Queueable;

    public $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("New Comment Posted in Joni's Blog")
            ->line('A new comment was posted:')
            ->line($this->comment->content)
            ->action('View Post', url('/posts/' . $this->comment->post_id));
    }
}
