<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfoBanner extends Model
{
    protected $fillable = ['message', 'is_visible'];
}
