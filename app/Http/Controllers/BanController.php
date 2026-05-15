<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\BanLog;
use Mchev\Banhammer\IP;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class BanController extends Controller
{
    // Dashboard with enhanced features
    public function index(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by role
        if ($request->role) {
            $query->where('role', $request->role);
        }

        $users = $query->paginate(15);

        // Filter by status
        if ($request->status == 'banned') {
            $users = $users->filter(fn($user) => $user->isBanned());
        } elseif ($request->status == 'active') {
            $users = $users->filter(fn($user) => !$user->isBanned());
        }

        // Get banned IPs
        $bannedIPs = BanLog::where('type', 'ip')
            ->where('status', 'banned')
            ->latest()
            ->take(10)
            ->get();

        // Get recent bans
        $recentBans = BanLog::where('type', 'user')
            ->with('user')
            ->latest()
            ->take(5)
            ->get();

        // Statistics
        $totalBans = BanLog::where('status', 'banned')->count();
        $totalIPBans = BanLog::where('type', 'ip')->where('status', 'banned')->count();
        $expiringSoon = BanLog::where('status', 'banned')
            ->where('expired_at', '<=', now()->addDays(1))
            ->where('expired_at', '>', now())
            ->count();

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
            'search' => $request->search,
            'status' => $request->status,
            'role' => $request->role
        ]);
    }

    // Ban User with duration
    public function banUser(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|min:3|max:500',
            'duration' => 'required|in:1,3,7,30,permanent'
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

        return redirect()->route('dashboard')
            ->with('success', 'User banned successfully for ' . ($request->duration == 'permanent' ? 'permanently' : $request->duration . ' days'));
    }

    // Unban User
    public function unbanUser($id)
    {
        $user = User::findOrFail($id);

        $user->unban();

        BanLog::where('user_id', $user->id)
            ->where('status', 'banned')
            ->latest()
            ->first()?->update([
                'status' => 'unbanned',
                'unbanned_at' => now()
            ]);

        return redirect()->route('dashboard')->with('success', 'User unbanned successfully');
    }

    // Ban Multiple Users
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
                $user->ban([
                    'comment' => $request->reason,
                    'expired_at' => $expired
                ]);

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

    // Extend Ban
    public function extendBan(Request $request, $id)
    {
        $request->validate([
            'extra_days' => 'required|integer|min:1|max:30'
        ]);

        $banLog = BanLog::findOrFail($id);
        
        if ($banLog->expired_at) {
            $newExpiry = Carbon::parse($banLog->expired_at)->addDays($request->extra_days);
            $banLog->update(['expired_at' => $newExpiry]);
            
            // Update user ban expiry
            $user = User::find($banLog->user_id);
            if ($user && $user->isBanned()) {
                $user->ban([
                    'expired_at' => $newExpiry
                ]);
            }
            
            return redirect()->route('history')->with('success', "Ban extended by {$request->extra_days} days");
        }
        
        return redirect()->route('history')->with('error', 'Cannot extend permanent ban');
    }

    // Ban IP with reason
    public function banIP(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'reason' => 'required|string|min:3'
        ]);

        // Check if IP already banned
        $existingBan = BanLog::where('ip', $request->ip)
            ->where('status', 'banned')
            ->exists();

        if ($existingBan) {
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

    // Ban History with filters
    public function history(Request $request)
    {
        $query = BanLog::latest();

        // Filter by type
        if ($request->type && $request->type != 'all') {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->status && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('user_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('ip', 'like', '%' . $request->search . '%')
                  ->orWhere('reason', 'like', '%' . $request->search . '%');
            });
        }

        // Date range filter
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

    // Delete Ban Log
    public function deleteBanLog($id)
    {
        $banLog = BanLog::findOrFail($id);
        $banLog->delete();

        return redirect()->route('history')->with('success', 'Ban log deleted successfully');
    }

    // Export Bans
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

    // API Stats
    public function apiStats()
    {
        return response()->json([
            'total_users' => User::count(),
            'banned_users' => User::all()->filter(fn($u) => $u->isBanned())->count(),
            'active_users' => User::all()->filter(fn($u) => !$u->isBanned())->count(),
            'total_bans' => BanLog::where('status', 'banned')->count(),
            'total_ip_bans' => BanLog::where('type', 'ip')->count(),
            'recent_bans' => BanLog::latest()->take(10)->get(),
            'expiring_soon' => BanLog::where('expired_at', '<=', now()->addDays(2))
                ->where('expired_at', '>', now())
                ->count()
        ]);
    }

    // Send Notification to User
    public function sendNotification(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:500'
        ]);

        $user = User::findOrFail($id);
        
        // Store notification in database
        DB::table('user_notifications')->insert([
            'user_id' => $user->id,
            'message' => $request->message,
            'type' => 'ban_warning',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Here you can also send email notification
        // Mail::to($user->email)->send(new BanWarningMail($request->message));

        return redirect()->route('dashboard')->with('success', 'Notification sent to user');
    }
}