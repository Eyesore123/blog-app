<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationPromptController extends Controller
{
    /**
     * Show the email verification prompt page.
     */
    public function __invoke(Request $request): Response|RedirectResponse
{
    if ($request->user()->hasVerifiedEmail()) {
        return redirect()->route('login.success');
    }

    return $request->session()->get('intended') === route('login.success')
        ? Inertia::render('auth/login', ['successMessage' => 'Email verified successfully! You can now log in.'])
        : Inertia::render('auth/verify-email', ['status' => $request->session()->get('status')]);
}
}
