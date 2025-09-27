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

    /**
     * Determine which channels the notification will be sent through.
     */
    public function via($notifiable)
    {
        // Send to mail only if the admin wants notifications
        return $notifiable->notify_comments ? ['mail'] : [];
    }

    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Comment Posted on Blog')
            ->line('A new comment was posted by: ' . ($this->comment->user->name ?? 'Anonymous'))
            ->line('Comment content: ' . $this->comment->content)
            ->action('View Post', url('/posts/' . $this->comment->post_id));
    }

    /**
     * Representation for database (optional).
     */
    public function toArray($notifiable)
    {
        return [
            'comment_id' => $this->comment->id,
            'post_id' => $this->comment->post_id,
            'content' => $this->comment->content,
            'user_name' => $this->comment->user->name ?? 'Anonymous',
        ];
    }
}
