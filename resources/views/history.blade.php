<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Ban History - Banhammer System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: #f0f2f5;
        }
        
        .navbar-custom {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .history-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-top: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            border-left: 4px solid;
            margin-bottom: 15px;
        }
        
        .filter-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .badge-user {
            background: #0d6efd;
        }
        
        .badge-ip {
            background: #6c757d;
        }
    </style>
</head>

<body>

<nav class="navbar navbar-custom mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/">
            <i class="fas fa-gavel text-danger me-2"></i>
            Banhammer System
        </a>
        <div class="d-flex gap-2">
            <a href="/" class="btn btn-primary">
                <i class="fas fa-dashboard"></i> Dashboard
            </a>
            <button class="btn btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">
            <i class="fas fa-history me-2 text-primary"></i>
            Ban History
        </h2>
        <a href="{{ route('bans.export', ['format' => 'csv']) }}" class="btn btn-success">
            <i class="fas fa-download"></i> Export CSV
        </a>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card" style="border-left-color: #0d6efd;">
                <h6 class="text-muted">Total Bans</h6>
                <h3 class="fw-bold">{{ $stats['total_bans'] ?? 0 }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card" style="border-left-color: #dc3545;">
                <h6 class="text-muted">Active Bans</h6>
                <h3 class="fw-bold text-danger">{{ $stats['active_bans'] ?? 0 }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card" style="border-left-color: #0dcaf0;">
                <h6 class="text-muted">User Bans</h6>
                <h3 class="fw-bold">{{ $stats['user_bans'] ?? 0 }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card" style="border-left-color: #6c757d;">
                <h6 class="text-muted">IP Bans</h6>
                <h3 class="fw-bold">{{ $stats['ip_bans'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filter-section">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" 
                       placeholder="Search..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="all">All Types</option>
                    <option value="user" {{ request('type') == 'user' ? 'selected' : '' }}>User Bans</option>
                    <option value="ip" {{ request('type') == 'ip' ? 'selected' : '' }}>IP Bans</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="all">All Status</option>
                    <option value="banned" {{ request('status') == 'banned' ? 'selected' : '' }}>Banned</option>
                    <option value="unbanned" {{ request('status') == 'unbanned' ? 'selected' : '' }}>Unbanned</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="from_date" class="form-control" placeholder="From Date" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="to_date" class="form-control" placeholder="To Date" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
    
    <!-- History Table -->
    <div class="history-container">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>User/IP</th>
                        <th>Email</th>
                        <th>Reason</th>
                        <th>Duration</th>
                        <th>Expiry</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->id }}</td>
                        <td>
                            @if($log->type == 'user')
                                <span class="badge bg-primary">
                                    <i class="fas fa-user"></i> User
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-ip"></i> IP
                                </span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $log->user_name ?? $log->ip ?? '-' }}</strong>
                        </td>
                        <td>{{ $log->email ?? '-' }}</td>
                        <td>{{ Str::limit($log->reason, 50) }}</td>
                        <td>
                            @if($log->duration)
                                @if($log->duration == 'permanent')
                                    <span class="badge bg-danger">Permanent</span>
                                @else
                                    <span class="badge bg-info">{{ $log->duration }} days</span>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($log->expired_at)
                                {{ \Carbon\Carbon::parse($log->expired_at)->format('d M Y') }}
                                @if($log->status == 'banned' && $log->expired_at > now())
                                    <small class="text-muted d-block">
                                        Expires in {{ now()->diffInDays($log->expired_at) }} days
                                    </small>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($log->status == 'banned')
                                <span class="badge bg-danger">
                                    <i class="fas fa-ban"></i> Banned
                                </span>
                            @else
                                <span class="badge bg-success">
                                    <i class="fas fa-check"></i> Unbanned
                                </span>
                            @endif
                        </td>
                        <td>{{ $log->created_at->format('d M Y h:i A') }}</td>
                        <td>
                            @if($log->status == 'banned' && $log->type == 'user')
                                <button class="btn btn-sm btn-outline-warning" onclick="showExtendModal({{ $log->id }})">
                                    <i class="fas fa-clock"></i>
                                </button>
                            @endif
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteLog({{ $log->id }})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No ban records found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{ $logs->links() }}
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function deleteLog(id) {
        if(confirm('Are you sure you want to delete this ban record?')) {
            fetch('/ban-log/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            }).then(() => location.reload());
        }
    }
</script>

</body>
</html>