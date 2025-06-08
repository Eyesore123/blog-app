<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sketch extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'topic',
        'published',
        'image',
        'tags',
    ];

    protected $casts = [
        'tags' => 'array',
        'published' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}