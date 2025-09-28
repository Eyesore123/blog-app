<?php
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\CustomAdminMessage;
use App\Http\Controllers\Controller;

class AdminEmailController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'group' => 'required|string|in:me,admins,subs,all',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $query = match ($request->group) {
            'admins' => User::where('is_admin', true),
            'subs' => User::where('is_subscribed', true),
            'all' => User::query(),
        };

        $users = $query->get();

        foreach ($users as $user) {
            Mail::to($user->email)->queue(
                new CustomAdminMessage($request->subject, $request->message)
            );
        }

        return response()->json(['success' => true]);
    }
}
