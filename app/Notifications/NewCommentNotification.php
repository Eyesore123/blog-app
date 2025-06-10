<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Comment;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    public function via($notifiable)
    {
        return $notifiable->notify_comments ? ['mail'] : [];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Comment Posted')
            ->line('A new comment was posted:')
            ->line($this->comment->content)
            ->action('View Post', url('/posts/' . $this->comment->post_id));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
