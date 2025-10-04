<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;

class RateLimitService
{
    protected $limit = 10;

    protected function getIdentifier(): string
    {
        if (Auth::check()) {
            return 'user-' . Auth::id();
        }

        $anonId = Request::cookie('anonId');
        if ($anonId) {
            return 'guest-' . $anonId;
        }

        return 'guest-ip-' . Request::ip();
    }

    public function getRemainingComments()
    {
        if (Auth::check() && Auth::user()->is_admin) {
            return '∞';
        }

        $identifier = $this->getIdentifier();
        $key = $this->getCacheKey($identifier);
        $count = Cache::get($key, 0);

        return max(0, $this->limit - $count);
    }

    public function incrementCommentCount()
    {
        if (Auth::check() && Auth::user()->is_admin) {
            return;
        }

        $identifier = $this->getIdentifier();
        $key = $this->getCacheKey($identifier);
        $expiresAt = now()->endOfDay();

        Cache::add($key, 0, $expiresAt);
        Cache::increment($key);
    }

    protected function getCacheKey($identifier)
    {
        return "comments_count:{$identifier}:" . now()->format('Y-m-d');
    }
}
