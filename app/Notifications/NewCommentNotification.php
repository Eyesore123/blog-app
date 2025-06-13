<!-- Not in use anymore -->

<!-- If you want to use this, you need to change this block to commentcontroller.php:
 
// Notify admin for every comment
        try {
        $admin = User::where('is_admin', true)->first();
        if ($admin) {
            Log::info('Admin for notification:', ['admin' => $admin]);
            $admin->notify(new NewCommentNotification($comment));
        }
        } catch (\Exception $e) {
            Log::error('Failed to send comment notification: ' . $e->getMessage());
        }

        // Notify parent comment author if it is a reply and they want notifications
        
        if ($comment->parent_id) {
            $parentComment = Comment::find($comment->parent_id);
            if ($parentComment && $parentComment->user_id) {
                $parentUser = User::find($parentComment->user_id);
                if ($parentUser && $parentUser->notify_comments) {
                    try {
                        $parentUser->notify(new NewCommentNotification($comment));
                    } catch (\Exception $e) {
                        Log::error('Failed to send reply notification to user: ' . $e->getMessage());
                    }
                }
            }
        }

-->

<?php

// namespace App\Notifications;

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
