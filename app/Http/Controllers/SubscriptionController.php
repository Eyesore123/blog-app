<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function subscribe(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->back()->with('error', 'You must be logged in to subscribe.');
        }

        $user->update(['is_subscribed' => true]);

        return redirect()->back()->with('success', 'Subscribed successfully!');
    }

    public function unsubscribe(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->back()->with('error', 'You must be logged in to unsubscribe.');
        }

        $user->update(['is_subscribed' => false]);

        return redirect()->back()->with('success', 'Unsubscribed successfully!');
    }
}