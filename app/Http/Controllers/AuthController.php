<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Auth/SignInPage');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function loginAsAnonymous()
    {
        $anonymousUser = User::create([
            'name' => 'Anonymous' . Str::random(14), // create a unique anonymous name
            'email' => 'anonymous@example.com',
            'password' => bcrypt(Str::random(10)), // create a random password
            'anonymous_id' => Str::uuid(), // generate a unique anonymous ID
        ]);

        Auth::login($anonymousUser);  // Log the anonymous user in

        return redirect()->route('home');  // Redirect the user to the home page
    }

    public function showRegister()
    {
        return Inertia::render('Auth/Register');
    }

   public function register(Request $request)
{
    $request->validate([
        'email' => ['required', 'email', 'unique:users,email'],
        'password' => ['required', 'confirmed', 'min:6'],
    ]);

    // Check if this is an anonymous registration
    $isAnonymous = $request->has('anonymous'); // You can pass a flag for anonymous registration

    $user = User::create([
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'name' => $isAnonymous ? 'Anonymous' . Str::random(14) : 'Default Name',  // Assign the anonymous name
        'anonymous_id' => $isAnonymous ? Str::uuid() : null,  // Assign anonymous ID only if it's an anonymous user
    ]);

    Auth::login($user);
    return redirect('/');
}


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->back();
    }
}

