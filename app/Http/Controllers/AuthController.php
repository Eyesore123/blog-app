<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Auth\Events\Registered;

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
            $user = Auth::user();

            if (! $user->hasVerifiedEmail()) {
                Auth::logout();
                return redirect('/verifyemailnotice')->with('email', $user->email);
            }

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
            'name' => 'Anonymous' . Str::random(14),
            'email' => 'anonymous@example.com',
            'password' => bcrypt(Str::random(10)),
            'anonymous_id' => Str::uuid(),
        ]);

        Auth::login($anonymousUser);
        session()->regenerate(); // regenerate session after login for safety

        return redirect()->route('home');
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
        'name' => ['nullable', 'string', 'max:255'],
        'profile_photo' => ['nullable', 'image', 'max:2048'],
    ]);

    // Check if this is an anonymous registration
    $isAnonymous = $request->has('anonymous');

    $profilePhotoPath = null;
    if ($request->hasFile('profile_photo')) {
        $profilePhotoPath = $request->file('profile_photo')->store('profile_photos', 'public');
    }

    $user = User::create([
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'name' => $isAnonymous ? 'Anonymous' . Str::random(14) : 'Default Name',  // Assign the anonymous name
        'anonymous_id' => $isAnonymous ? Str::uuid() : null,  // Assign anonymous ID only if it's an anonymous user
        'profile_photo_path' => $profilePhotoPath,
    ]);

    event(new Registered($user));

    return redirect('/verifyemailnotice')->with('email', $user->email);

}


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->back();
    }
}

