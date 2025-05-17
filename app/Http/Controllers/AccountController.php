<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Subscription;
use Inertia\Inertia;
use App\Models\Post;
use App\Models\Comment;
use App\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Log;
use App\Services\RateLimitService;

class AccountController extends Controller
{
     private $rateLimiter;

    public function __construct(RateLimitService $rateLimiter)
    {
        $this->rateLimiter = $rateLimiter;
    }

public function show(Request $request)
{
    $query = Post::query();

    // Filter by search term
    if ($request->has('search') && $request->search) {
        $query->where('title', 'like', '%' . $request->search . '%')
              ->orWhere('content', 'like', '%' . $request->search . '%');
    }

    // Filter by year
    if ($request->has('year') && $request->year) {
        $query->whereYear('created_at', $request->year);
    }

    // Paginate the results
    $allPosts = $query->paginate(10);

    return Inertia::render('AccountPage', [
        'user' => Auth::user(),
        'allPosts' => $allPosts,
    ]);
}

    // Update email
    public function updateEmail(Request $request)
{
    $request->validate([
        'email' => 'required|email|unique:users,email',
    ]);

    $user = Auth::user();
    $user->email = $request->input('email');
    $user->save();

    if ($request->wantsJson()) {
        return response()->json(['message' => 'Email updated successfully']);
    }

    return back()->with('success', 'Email updated successfully');
}

    // Update password
public function updatePassword(Request $request)
{
    $request->validate([
        'current_password' => 'required',
        'password' => 'required|min:8|confirmed',
    ]);

    $user = Auth::user();

    if (!Hash::check($request->input('current_password'), $user->password)) {
        return back()->withErrors(['current_password' => 'The current password is incorrect.']);
    }

    $user->password = Hash::make($request->input('password'));
    $user->save();

    return back()->with('success', 'Password updated successfully');
}

    // Delete account
   public function deleteAccount(Request $request, RateLimitService $rateLimiter)
    {
        $user = Auth::user();
        if ($request->input('remove_comments') === 'yes') {
            Log::info('Remove comments input: ' . $request->input('remove_comments'));
            $commentController = new CommentController($rateLimiter);
            $commentController->removeCommentsForUser($user->id);
        } else {
            // Do not delete comments
        }
        $user->delete();

        return redirect()->route('home')->with('success', 'Account deleted successfully');
    }

    // Test account deletion:
//     public function deleteAccount(Request $request)
// {
//     $user = Auth::user();
//     $user->detach();
//     $user->delete();

//     return redirect()->route('home')->with('success', 'Account deleted successfully');
// }

    // Toggle newsletter status

    public function toggleNewsletterSubscription(Request $request)
    {
        $user = Auth::user();
        $user->is_subscribed = !$user->is_subscribed;
        $user->save();

        $message = $user->is_subscribed
            ? 'Subscribed to newsletters'
            : 'Unsubscribed from newsletters';

        return back()->with('success', $message);
    }

    // Unsubscribe from newsletters
    public function unsubscribeNewsletter()
    {
        $user = Auth::user();
        $subscription = Subscription::where('user_id', $user->id)->first();

        if ($subscription) {
            $subscription->delete();
        }

        return back()->with('success', 'Unsubscribed from newsletters');
    }
}
