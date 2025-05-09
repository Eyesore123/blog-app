<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['title', 'content', 'published', 'topic', 'image_path', 'user_id,', 'slug', 'created_at', 'updated_at'];

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
