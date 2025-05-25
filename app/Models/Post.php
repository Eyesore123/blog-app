<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use App\Models\Comment;
use App\Models\Tag;

class Post extends Model
{
    protected $fillable = ['title', 'content', 'published', 'topic', 'image_path', 'user_id', 'slug', 'created_at', 'updated_at', 'postUrl', 'sent_at'];

    public static function boot()
    {
        parent::boot();

        static::saving(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

     public function getPostUrlAttribute()
    {
        return route('posts.show', $this->id);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
    
    public function getImageUrlAttribute($value)
    {
        // If it's already a full URL, return as-is
        if (str_starts_with($value, 'http')) {
            return $value;
        }
        
        // If it's a relative path, return the route URL
        if ($value) {
            return route('image.show', ['filename' => $value]);
        }
        
        return null;
    }


}