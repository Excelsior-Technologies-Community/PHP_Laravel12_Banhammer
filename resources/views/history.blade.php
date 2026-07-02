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
        * { font-family: 'Inter', sans-serif; }
        body { background: #f0f2f5; }
        
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
            transition: transform 0.2s;
        }
        .stats-card:hover { transform: translateY(-3px); }
        
        .filter-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .badge-user { background: #0d6efd; }
        .badge-ip { background: #6c757d; }
        
        .appeal-badge-pending { background: #ffc107; color: #000; }
        .appeal-badge-approved { background: #198754; color: #fff; }
        .appeal-badge-rejected { background: #dc3545; color: #fff; }
        .appeal-badge-none { background: #6c757d; color: #fff; }
        
        .warning-level-1 { background: #fd7e14; color: #fff; }
        .warning-level-2 { background: #dc3545; color: #fff; }
        .warning-level-3 { background: #6f42c1; color: #fff; }
        
        .modal-content { border-radius: 15px; }
        .modal-header { border-bottom: none; }
        .modal-footer { border-top: none; }
        
        .note-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
            border-left: 3px solid #0d6efd;
        }
        .note-item.internal { border-left-color: #6c757d; }
        .note-item .note-meta {
            font-size: 11px;
            color: #6c757d;
        }
        
        .appeal-box {
            background: #e7f3ff;
            border-radius: 8px;
            padding: 15px;
            border-left: 4px solid #0d6efd;
        }
        
        .appeal-box.rejected { background: #ffe7e7; border-left-color: #dc3545; }
        .appeal-box.approved { background: #e7ffe7; border-left-color: #198754; }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }
        .status-banned { background: #dc3545; color: #fff; }
        .status-unbanned { background: #198754; color: #fff; }
        .status-expired { background: #ffc107; color: #000; }
        
        .type-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .duration-badge {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
        }
        
        .btn-action {
            padding: 4px 10px;
            font-size: 12px;
            border-radius: 6px;
        }
        
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            cursor: pointer;
        }
        
        .search-highlight {
            background: #ffeb3b;
            padding: 0 2px;
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
            <a href="/" class="btn btn-primary btn-sm">
                <i class="fas fa-dashboard"></i> Dashboard
            </a>
            <a href="{{ route('appeals.index') }}" class="btn btn-warning btn-sm">
                <i class="fas fa-gavel"></i> Appeals
            </a>
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="fas fa-print"></i>
            </button>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">
            <i class="fas fa-history me-2 text-primary"></i>
            Ban History
            <span class="badge bg-secondary ms-2">{{ $logs->total() }}</span>
        </h2>
        <div class="d-flex gap-2">
            <a href="{{ route('bans.export', ['format' => 'csv']) }}" class="btn btn-success btn-sm">
                <i class="fas fa-download"></i> Export CSV
            </a>
        </div>
    </div>
    
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
    
    <div class="filter-section">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" 
                       placeholder="Search by user, email, IP or reason..." 
                       value="{{ request('search') }}">
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
                <select name="appeal_status" class="form-select">
                    <option value="all">All Appeals</option>
                    <option value="pending" {{ request('appeal_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('appeal_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('appeal_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="none" {{ request('appeal_status') == 'none' ? 'selected' : '' }}>No Appeal</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
    
    <div class="history-container">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>User/IP</th>
                        <th>Email</th>
                        <th>Reason</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Appeal</th>
                        <th>Warning</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr onclick="window.location='{{ route('ban.details', $log->id) }}'" style="cursor:pointer;">
                        <td>{{ $log->id }}</td>
                        <td>
                            @if($log->type == 'user')
                                <span class="badge bg-primary">
                                    <i class="fas fa-user"></i> User
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-network-wired"></i> IP
                                </span>
                            @endif
                            @if($log->auto_ban)
                                <span class="badge bg-warning text-dark" title="Auto-banned">🤖</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $log->user_name ?? $log->ip ?? '-' }}</strong>
                        </td>
                        <td>{{ $log->email ?? '-' }}</td>
                        <td>
                            <span title="{{ $log->reason }}">
                                {{ Str::limit($log->reason, 40) }}
                            </span>
                        </td>
                        <td>
                            @if($log->duration)
                                @if($log->duration == 'permanent')
                                    <span class="badge bg-danger">♾️ Permanent</span>
                                @else
                                    <span class="badge bg-info">{{ $log->duration }} days</span>
                                    @if($log->expired_at && $log->status == 'banned')
                                        <small class="d-block text-muted">
                                            {{ now()->diffInDays($log->expired_at) }} days left
                                        </small>
                                    @endif
                                @endif
                            @else
                                <span class="badge bg-secondary">—</span>
                            @endif
                        </td>
                        <td>
                            @if($log->status == 'banned')
                                @if($log->expired_at && $log->expired_at <= now())
                                    <span class="badge bg-warning">⏰ Expired</span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="fas fa-ban"></i> Banned
                                    </span>
                                @endif
                            @else
                                <span class="badge bg-success">
                                    <i class="fas fa-check"></i> Unbanned
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($log->appeal_status == 'pending')
                                <span class="badge bg-warning text-dark">⏳ Pending</span>
                            @elseif($log->appeal_status == 'approved')
                                <span class="badge bg-success">✅ Approved</span>
                            @elseif($log->appeal_status == 'rejected')
                                <span class="badge bg-danger">❌ Rejected</span>
                            @else
                                <span class="badge bg-secondary">—</span>
                            @endif
                        </td>
                        <td>
                            @if($log->warning_level > 0)
                                @if($log->warning_level == 1)
                                    <span class="badge bg-warning text-dark">⚠️ L1</span>
                                @elseif($log->warning_level == 2)
                                    <span class="badge bg-orange">⚠️ L2</span>
                                @elseif($log->warning_level == 3)
                                    <span class="badge bg-danger">🚫 L3</span>
                                @endif
                            @else
                                <span class="badge bg-secondary">—</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $log->created_at->format('d M Y') }}</small><br>
                            <small class="text-muted" style="font-size:10px;">{{ $log->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <a href="{{ route('ban.details', $log->id) }}" class="btn btn-sm btn-outline-primary btn-action">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($log->status == 'banned' && $log->type == 'user')
                                    <button class="btn btn-sm btn-outline-warning btn-action" onclick="event.stopPropagation(); showExtendModal({{ $log->id }})">
                                        <i class="fas fa-clock"></i>
                                    </button>
                                @endif
                                <button class="btn btn-sm btn-outline-danger btn-action" onclick="event.stopPropagation(); deleteLog({{ $log->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No ban records found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} records
            </div>
            <div>
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function deleteLog(id) {
        event.stopPropagation();
        if(confirm('Are you sure you want to delete this ban record?')) {
            fetch('/ban-log/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            }).then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert('Error deleting record');
                }
            }).catch(() => {
                alert('Error deleting record');
            });
        }
    }

    function showExtendModal(id) {
        event.stopPropagation();
        // Implement extend modal logic
        const days = prompt('Enter number of days to extend (1-30):');
        if(days && days > 0 && days <= 30) {
            fetch('/extend-ban/' + id, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ extra_days: parseInt(days) })
            }).then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error extending ban');
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="search"]');
        if(searchInput && searchInput.value) {
            const highlight = searchInput.value;
            document.querySelectorAll('td').forEach(td => {
                const text = td.textContent;
                if(text.toLowerCase().includes(highlight.toLowerCase())) {
                    td.innerHTML = text.replace(
                        new RegExp(highlight, 'gi'),
                        match => `<span class="search-highlight">${match}</span>`
                    );
                }
            });
        }
    });
</script>

</body>
</html>