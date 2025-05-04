<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        Post::create([
            'title' => 'First post',
            'topic' => 'General',
            'content' => 'Hello, this is my first post!',
        ]);

        Post::create([
            'title' => 'Second post',
            'topic' => 'Programming',
            'content' => 'Here is a post about Laravel.',
        ]);
    }
}
