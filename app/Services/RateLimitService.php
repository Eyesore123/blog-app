<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;

class RateLimitService
{
    protected $limit = 10;

    public function getRemainingComments()
    {
        // Use user ID if authenticated, otherwise use IP address
        $identifier = Auth::check() ? Auth::id() : Request::ip();

        // Skip rate limiting for admin users
        if (Auth::check() && Auth::user()->is_admin) {
            return '∞';
        }

        $key = $this->getCacheKey($identifier);
        $count = Cache::get($key, 0);

        return max(0, $this->limit - $count); // Return how many comments the user can still make
    }

    public function incrementCommentCount()
    {
        // Skip rate limiting for admin users
        if (Auth::check() && Auth::user()->is_admin) {
            return;
        }

        // Use user ID if authenticated, otherwise use IP address
        $identifier = Auth::check() ? Auth::id() : Request::ip();
        $key = $this->getCacheKey($identifier);
        $expiresAt = now()->endOfDay();

        // Ensure the cache key exists and increment the comment count
        Cache::add($key, 0, $expiresAt); // Ensure key exists
        Cache::increment($key);
    }

    protected function getCacheKey($identifier)
    {
        return "comments_count:{$identifier}:" . now()->format('Y-m-d');
    }
}
