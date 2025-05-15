<?php
namespace App\Console\Commands;

use App\Models\Post;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewPostNotification;

class SendNewPostEmails extends Command
{
    protected $signature = 'emails:send-new-posts';
    protected $description = 'Send email notifications to subscribers for new posts';

    public function handle()
    {
        // Fetch posts that haven't been sent yet
        $posts = Post::whereNull('sent_at')->get();

        if ($posts->isEmpty()) {
            $this->info('No new posts to send.');
            return 0;
        }

        // Fetch all subscribed users
        $subscribers = User::where('is_subscribed', 1)->get();

        foreach ($posts as $post) {
            foreach ($subscribers as $subscriber) {
                Mail::to($subscriber->email)->send(new NewPostNotification($post));
            }

            // Mark the post as sent
            $post->update(['sent_at' => now()]);
        }

        $this->info('Emails sent for ' . $posts->count() . ' new post(s).');
        return 0;
    }
}