<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class AuthNoticeController extends Controller
{
    public function verifyEmailNotice(Request $request)
    {
        return redirect()->route('login/success')->with('message', 'Email verified successfully! You can now log in.');
    }
}