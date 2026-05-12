<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\BanLog;
use Mchev\Banhammer\IP;

class BanController extends Controller
{
    // Dashboard
    public function index(Request $request)
    {
        $query = User::query();

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->get();

        // Filter
        if ($request->status == 'banned') {
            $users = $users->filter(fn($user) => $user->isBanned());
        }

        if ($request->status == 'active') {
            $users = $users->filter(fn($user) => !$user->isBanned());
        }

        return view('welcome', [
            'users' => $users,
            'totalUsers' => User::count(),
            'bannedUsers' => User::all()->filter(fn($u) => $u->isBanned())->count(),
            'activeUsers' => User::all()->filter(fn($u) => !$u->isBanned())->count(),
        ]);
    }

    // Ban User
    public function banUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $reason = $request->reason ?? 'Violation of rules';

        $expired = now()->addDays(2);

        $user->ban([
            'comment' => $reason,
            'expired_at' => $expired
        ]);

        BanLog::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'email' => $user->email,
            'reason' => $reason,
            'expired_at' => $expired,
            'status' => 'banned'
        ]);

        return back()->with('success', 'User banned successfully');
    }

    // Unban User
    public function unbanUser($id)
    {
        $user = User::findOrFail($id);

        $user->unban();

        BanLog::where('user_id', $user->id)
            ->latest()
            ->first()?->update([
                'status' => 'unbanned'
            ]);

        return back()->with('success', 'User unbanned successfully');
    }

    // Ban IP
    public function banIP(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip'
        ]);

        IP::ban($request->ip);

        BanLog::create([
            'ip' => $request->ip,
            'reason' => 'IP Address banned',
            'status' => 'banned'
        ]);

        return back()->with('success', 'IP banned successfully');
    }

    // Ban History
    public function history()
    {
        $logs = BanLog::latest()->get();

        return view('history', compact('logs'));
    }
}