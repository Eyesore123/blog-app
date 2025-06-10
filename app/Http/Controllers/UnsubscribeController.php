<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UnsubscribeController extends Controller
{
    public function unsubscribe(Request $request)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired unsubscribe link.');
        }

        $email = $request->query('email');
        $type = $request->query('type');

        // Update user preferences
        $user = \App\Models\User::where('email', $email)->first();
        if ($user) {
            if ($type === 'post') {
                $user->is_subscribed = false;
            } elseif ($type === 'comment') {
                $user->notify_comments = false;
            }
            $user->save();
        }

        return view('unsubscribe-success');
    }
}