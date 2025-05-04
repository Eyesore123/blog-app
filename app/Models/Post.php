<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    // These are the fields we allow to be mass assigned (for create / update)
    protected $fillable = ['title', 'topic', 'content'];
}
