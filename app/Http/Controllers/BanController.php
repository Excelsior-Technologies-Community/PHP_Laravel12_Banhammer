<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\BanLog;
use App\Models\Warning;
use App\Models\Appeal;
use App\Models\BanNote;
use Mchev\Banhammer\IP;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BanController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->role) {
            $query->where('role', $request->role);
        }

        $users = $query->paginate(15);

        if ($request->status == 'banned') {
            $users = $users->filter(fn($user) => $user->isBanned());
        } elseif ($request->status == 'active') {
            $users = $users->filter(fn($user) => !$user->isBanned());
        }

        $bannedIPs = BanLog::where('type', 'ip')->where('status', 'banned')->latest()->take(10)->get();
        $recentBans = BanLog::where('type', 'user')->with('user')->latest()->take(5)->get();

        $totalBans = BanLog::where('status', 'banned')->count();
        $totalIPBans = BanLog::where('type', 'ip')->where('status', 'banned')->count();
        $expiringSoon = BanLog::where('status', 'banned')
            ->where('expired_at', '<=', now()->addDays(1))
            ->where('expired_at', '>', now())
            ->count();

        $warningStats = [
            'total' => Warning::count(),
            'active' => Warning::where('status', 'active')->count(),
            'level3' => Warning::where('level', 3)->where('status', 'active')->count(),
        ];

        $appealStats = [
            'pending' => Appeal::where('status', 'pending')->count(),
            'approved' => Appeal::where('status', 'approved')->count(),
            'rejected' => Appeal::where('status', 'rejected')->count(),
        ];

        return view('welcome', [
            'users' => $users,
            'totalUsers' => User::count(),
            'bannedUsers' => User::all()->filter(fn($u) => $u->isBanned())->count(),
            'activeUsers' => User::all()->filter(fn($u) => !$u->isBanned())->count(),
            'bannedIPs' => $bannedIPs,
            'recentBans' => $recentBans,
            'totalBans' => $totalBans,
            'totalIPBans' => $totalIPBans,
            'expiringSoon' => $expiringSoon,
            'warningStats' => $warningStats,
            'appealStats' => $appealStats,
            'search' => $request->search,
            'status' => $request->status,
            'role' => $request->role
        ]);
    }

    public function banUser(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|min:3|max:500',
            'duration' => 'required|in:1,3,7,30,permanent',
            'warning_level' => 'nullable|integer|min:0|max:3'
        ]);

        $user = User::findOrFail($id);

        $durationMap = [
            '1' => now()->addDay(),
            '3' => now()->addDays(3),
            '7' => now()->addDays(7),
            '30' => now()->addDays(30),
            'permanent' => null
        ];

        $expired = $durationMap[$request->duration];

        $user->ban([
            'comment' => $request->reason,
            'expired_at' => $expired
        ]);

        $banLog = BanLog::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'email' => $user->email,
            'reason' => $request->reason,
            'expired_at' => $expired,
            'duration' => $request->duration,
            'status' => 'banned',
            'type' => 'user',
            'banned_by' => auth()->id() ?? 1,
            'ip_address' => $request->ip(),
            'warning_level' => $request->warning_level ?? 0,
            'auto_ban' => $request->warning_level >= 3
        ]);

        if ($request->warning_level >= 3) {
            Warning::where('user_id', $user->id)->where('status', 'active')->update(['status' => 'escalated']);
        }

        return redirect()->route('dashboard')->with('success', 'User banned successfully');
    }

    public function issueWarning(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|min:3|max:500',
            'level' => 'required|in:1,2,3'
        ]);

        $user = User::findOrFail($id);

        $existingWarnings = Warning::where('user_id', $user->id)->where('status', 'active')->count();

        if ($existingWarnings >= 3) {
            return redirect()->route('dashboard')->with('error', 'User already has 3 active warnings. Auto-banning...');
        }

        $warning = Warning::create([
            'user_id' => $user->id,
            'issued_by' => auth()->id() ?? 1,
            'reason' => $request->reason,
            'level' => $request->level,
            'status' => 'active',
            'expires_at' => now()->addDays(30)
        ]);

        $totalWarnings = Warning::where('user_id', $user->id)->where('status', 'active')->count();

        if ($totalWarnings >= 3) {
            $this->autoBanUser($user, 'Auto-banned due to 3 warnings');
            return redirect()->route('dashboard')->with('success', 'Warning issued. User auto-banned due to 3 warnings.');
        }

        return redirect()->route('dashboard')->with('success', 'Warning issued successfully. (Warning ' . $totalWarnings . '/3)');
    }

    private function autoBanUser($user, $reason)
    {
        $user->ban(['comment' => $reason]);

        BanLog::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'email' => $user->email,
            'reason' => $reason,
            'expired_at' => now()->addDays(7),
            'duration' => '7',
            'status' => 'banned',
            'type' => 'user',
            'banned_by' => 1,
            'ip_address' => request()->ip(),
            'auto_ban' => true
        ]);

        Warning::where('user_id', $user->id)->where('status', 'active')->update(['status' => 'escalated']);
    }

    public function submitAppeal(Request $request, $banId)
    {
        $request->validate([
            'message' => 'required|string|min:10|max:1000'
        ]);

        $banLog = BanLog::findOrFail($banId);

        if ($banLog->appeal_status !== 'none' && $banLog->appeal_status !== 'rejected') {
            return redirect()->route('history')->with('error', 'Appeal already submitted or processed.');
        }

        $appeal = Appeal::create([
            'ban_log_id' => $banLog->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
            'status' => 'pending'
        ]);

        $banLog->update(['appeal_status' => 'pending']);

        return redirect()->route('history')->with('success', 'Appeal submitted successfully. Please wait for admin review.');
    }

    public function reviewAppeal(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_notes' => 'nullable|string|max:500'
        ]);

        $appeal = Appeal::findOrFail($id);
        $appeal->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => auth()->id() ?? 1,
            'reviewed_at' => now()
        ]);

        $banLog = BanLog::find($appeal->ban_log_id);
        if ($banLog) {
            $banLog->update(['appeal_status' => $request->status]);

            if ($request->status === 'approved') {
                $user = User::find($banLog->user_id);
                if ($user && $user->isBanned()) {
                    $user->unban();
                }
                $banLog->update(['status' => 'unbanned']);
            }
        }

        return redirect()->route('appeals.index')->with('success', 'Appeal ' . $request->status . ' successfully.');
    }

    public function addBanNote(Request $request, $banId)
    {
        $request->validate([
            'note' => 'required|string|min:3|max:500',
            'is_internal' => 'nullable|boolean'
        ]);

        BanNote::create([
            'ban_log_id' => $banId,
            'user_id' => auth()->id(),
            'note' => $request->note,
            'is_internal' => $request->is_internal ?? true
        ]);

        return redirect()->route('history')->with('success', 'Note added successfully.');
    }

    public function getBanDetails($id)
    {
        $banLog = BanLog::with(['user', 'banner'])->findOrFail($id);
        $notes = BanNote::where('ban_log_id', $id)->with('user')->get();
        $appeal = Appeal::where('ban_log_id', $id)->with(['user', 'reviewer'])->first();

        return view('ban-details', compact('banLog', 'notes', 'appeal'));
    }

    public function appealsIndex()
    {
        $appeals = Appeal::with(['user', 'banLog', 'reviewer'])->latest()->paginate(20);

        $stats = [
            'total' => Appeal::count(),
            'pending' => Appeal::where('status', 'pending')->count(),
            'approved' => Appeal::where('status', 'approved')->count(),
            'rejected' => Appeal::where('status', 'rejected')->count(),
        ];

        return view('appeals', compact('appeals', 'stats'));
    }

    public function unbanUser($id)
    {
        $user = User::findOrFail($id);
        $user->unban();

        BanLog::where('user_id', $user->id)->where('status', 'banned')->latest()->first()?->update([
            'status' => 'unbanned',
            'unbanned_at' => now()
        ]);

        return redirect()->route('dashboard')->with('success', 'User unbanned successfully');
    }

    public function banMultiple(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'reason' => 'required|string',
            'duration' => 'required|in:1,3,7,30,permanent'
        ]);

        $durationMap = [
            '1' => now()->addDay(),
            '3' => now()->addDays(3),
            '7' => now()->addDays(7),
            '30' => now()->addDays(30),
            'permanent' => null
        ];

        $expired = $durationMap[$request->duration];
        $bannedCount = 0;

        foreach ($request->user_ids as $userId) {
            $user = User::find($userId);
            if ($user && !$user->isBanned()) {
                $user->ban(['comment' => $request->reason, 'expired_at' => $expired]);
                BanLog::create([
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'email' => $user->email,
                    'reason' => $request->reason,
                    'expired_at' => $expired,
                    'duration' => $request->duration,
                    'status' => 'banned',
                    'type' => 'user',
                    'banned_by' => auth()->id() ?? 1,
                    'ip_address' => $request->ip()
                ]);
                $bannedCount++;
            }
        }

        return redirect()->route('dashboard')->with('success', "$bannedCount users banned successfully");
    }

    public function extendBan(Request $request, $id)
    {
        $request->validate(['extra_days' => 'required|integer|min:1|max:30']);
        $banLog = BanLog::findOrFail($id);

        if ($banLog->expired_at) {
            $newExpiry = Carbon::parse($banLog->expired_at)->addDays($request->extra_days);
            $banLog->update(['expired_at' => $newExpiry]);

            $user = User::find($banLog->user_id);
            if ($user && $user->isBanned()) {
                $user->ban(['expired_at' => $newExpiry]);
            }

            return redirect()->route('history')->with('success', "Ban extended by {$request->extra_days} days");
        }

        return redirect()->route('history')->with('error', 'Cannot extend permanent ban');
    }

    public function banIP(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'reason' => 'required|string|min:3'
        ]);

        if (BanLog::where('ip', $request->ip)->where('status', 'banned')->exists()) {
            return redirect()->route('dashboard')->with('error', 'IP already banned');
        }

        IP::ban($request->ip);

        BanLog::create([
            'ip' => $request->ip,
            'reason' => $request->reason,
            'status' => 'banned',
            'type' => 'ip',
            'banned_by' => auth()->id() ?? 1,
            'expired_at' => $request->permanent ? null : now()->addDays(30)
        ]);

        return redirect()->route('dashboard')->with('success', 'IP banned successfully');
    }

    public function history(Request $request)
    {
        $query = BanLog::latest();

        if ($request->type && $request->type != 'all') {
            $query->where('type', $request->type);
        }

        if ($request->status && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('user_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('ip', 'like', '%' . $request->search . '%')
                  ->orWhere('reason', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->paginate(20);

        $stats = [
            'total_bans' => BanLog::count(),
            'active_bans' => BanLog::where('status', 'banned')->count(),
            'user_bans' => BanLog::where('type', 'user')->count(),
            'ip_bans' => BanLog::where('type', 'ip')->count(),
        ];

        return view('history', compact('logs', 'stats'));
    }

    public function deleteBanLog($id)
    {
        BanLog::findOrFail($id)->delete();
        return redirect()->route('history')->with('success', 'Ban log deleted successfully');
    }

    public function exportBans(Request $request)
    {
        $query = BanLog::query();

        if ($request->format == 'csv') {
            $logs = $query->get();
            $filename = 'bans-export-' . date('Y-m-d') . '.csv';

            $handle = fopen('php://temp', 'w');
            fputcsv($handle, ['ID', 'User', 'Email', 'IP', 'Reason', 'Duration', 'Status', 'Created At']);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->id,
                    $log->user_name,
                    $log->email,
                    $log->ip,
                    $log->reason,
                    $log->duration,
                    $log->status,
                    $log->created_at
                ]);
            }

            rewind($handle);
            $csv = stream_get_contents($handle);
            fclose($handle);

            return response($csv, 200)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        }

        return redirect()->route('history')->with('error', 'Invalid export format');
    }

    public function apiStats()
    {
        return response()->json([
            'total_users' => User::count(),
            'banned_users' => User::all()->filter(fn($u) => $u->isBanned())->count(),
            'active_users' => User::all()->filter(fn($u) => !$u->isBanned())->count(),
            'total_bans' => BanLog::where('status', 'banned')->count(),
            'total_ip_bans' => BanLog::where('type', 'ip')->count(),
            'total_warnings' => Warning::count(),
            'active_warnings' => Warning::where('status', 'active')->count(),
            'pending_appeals' => Appeal::where('status', 'pending')->count(),
            'recent_bans' => BanLog::latest()->take(10)->get(),
            'expiring_soon' => BanLog::where('expired_at', '<=', now()->addDays(2))
                ->where('expired_at', '>', now())
                ->count()
        ]);
    }

    public function sendNotification(Request $request, $id)
    {
        $request->validate(['message' => 'required|string|max:500']);

        $user = User::findOrFail($id);

        DB::table('user_notifications')->insert([
            'user_id' => $user->id,
            'message' => $request->message,
            'type' => 'ban_warning',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->route('dashboard')->with('success', 'Notification sent to user');
    }
}