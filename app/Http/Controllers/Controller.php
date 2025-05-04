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
                'user' => auth()->user(),
            ],
            // Example of adding flash messages globally
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ];
    }
}
