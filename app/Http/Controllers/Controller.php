<?php
namespace App\Http\Controllers;
use Inertia\Inertia;

abstract class Controller
{
    public function __construct()
    {
        Inertia::share($this->sharedProps());
    }

    protected function sharedProps()
    {
        return [
            'auth' => [
                'user' => auth()->check() ? [
                    'id' => auth()->user()->id,
                    'name' => auth()->user()->name ?? '',
                    'email' => auth()->user()->email,
                    'anonymous_id' => auth()->user()->anonymous_id,
                    'is_admin' => auth()->user()->is_admin ?? false,
                    'is_anonymous' => !empty(auth()->user()->anonymous_id),
                    'profile_photo_path' => auth()->user()->profile_photo_path,
                ] : null,
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ];
    }
}
