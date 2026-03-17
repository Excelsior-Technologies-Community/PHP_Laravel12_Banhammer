<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Mchev\Banhammer\IP;

class BanController extends Controller
{
    // Show users
    public function index()
    {
        $users = User::all();
        return view('welcome', compact('users'));
    }

    // Ban user
    public function banUser($id)
    {
        $user = User::findOrFail($id);

        $user->ban([
            'comment' => 'You are banned by admin',
            'expired_at' => now()->addDays(2)
        ]);

        return back()->with('success', 'User banned');
    }

    // Unban user
    public function unbanUser($id)
    {
        $user = User::findOrFail($id);
        $user->unban();

        return back()->with('success', 'User unbanned');
    }

    // Ban IP
    public function banIP(Request $request)
    {
        IP::ban($request->ip);

        return back()->with('success', 'IP banned');
    }
}