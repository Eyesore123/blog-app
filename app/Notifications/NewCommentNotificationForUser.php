<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Comment;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewCommentNotificationForUser extends Notification implements ShouldQueue
{
    use Queueable;

    public $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    public function via($notifiable)
    {
        return ['mail']; // could also add 'database' if you want DB notifications
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("New Comment Response in Joni's Blog")
            ->line('Someone responded to your comment:')
            ->line($this->comment->content)
            ->action('View Post', url('/posts/' . $this->comment->post_id));
    }
}
