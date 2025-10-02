<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\CustomAdminMessage;
use Illuminate\Support\Facades\Log;

class AdminEmailController extends Controller
{
    // 1️⃣ Mass send to groups
    public function send(Request $request)
    {
        try {
            $request->validate([
                'group' => 'required|string|in:admins,subs,all',
                'subject' => 'required|string|max:255',
                'message' => 'required|string|max:5000',
            ]);

            $users = match ($request->group) {
                'admins' => User::where('is_admin', true)->get(),
                'subs'   => User::where('is_subscribed', true)->get(),
                'all'    => User::all(),
            };

            Log::info("Users to send: " . $users->pluck('email')->join(', '));

            foreach ($users as $user) {
                Mail::to($user->email)->queue(new CustomAdminMessage($request->subject, $request->message));
            }

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('Mass email failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to send emails: '.$e->getMessage()
            ], 500);
        }
    }


    // 2️⃣ Test email with custom subject/message
    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        Mail::to($request->email)->queue(
            new CustomAdminMessage($request->subject, $request->message)
        );

        Log::info("Test email queued to {$request->email}");

        return response()->json([
            'success' => true,
            'message' => "Test email queued to {$request->email}"
        ]);
    }

    // 3️⃣ Quick test post notification
    public function testPostNotification(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        Mail::to($request->email)->queue(
            new CustomAdminMessage('Test Post Notification', 'This is a test post notification email.')
        );

        Log::info("Test post notification sent to {$request->email}");

        return response()->json([
            'success' => true,
            'message' => "Test post notification sent to {$request->email}"
        ]);
    }
}
