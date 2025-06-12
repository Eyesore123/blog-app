<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewPostNotification;

class SendNewPostEmails extends Command
{
    protected $signature = 'emails:send-new-posts';
    protected $description = 'Send email notifications to subscribers for new posts';

    public function handle()
    {
        $posts = Post::where('created_at', '>=', now()->subHour())->get();

        if ($posts->isEmpty()) {
        $this->info('No new posts to send.');
        return 0;
    }

    $subscribers = User::where('is_subscribed', 1)->get();

    foreach ($posts as $post) {
        foreach ($subscribers as $subscriber) {
            Mail::to($subscriber->email)->send(new NewPostNotification($post));
        }
    }

    $this->info('Emails sent for ' . $posts->count() . ' new post(s).');
    return 0;
    }
}
