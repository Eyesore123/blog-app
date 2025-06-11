<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        Log::info('VerifyEmailController: __invoke called');

        if ($request->user()->hasVerifiedEmail()) {
            Log::info('Email is already verified');
            return redirect()->route('login.success')
                ->with('successMessage', 'Email was already verified.');
        }

        if ($request->user()->markEmailAsVerified()) {
            Log::info('Email verified successfully');
            event(new Verified($request->user()));

            return redirect()->route('login.success')
                ->with('successMessage', 'Email verified successfully! You can now log in.');
        }

        Log::info('Email is not verified');
        return redirect()->route('login');
    }


}
