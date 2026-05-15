<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Banhammer - User Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .navbar-custom {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .table thead th {
            background: #f8f9fa;
            color: #495057;
            font-weight: 600;
            border-bottom: 2px solid #e9ecef;
        }
        
        .table tbody tr {
            transition: background 0.2s ease;
        }
        
        .table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .btn-ban {
            background: linear-gradient(135deg, #dc3545, #c82333);
            border: none;
            color: white;
        }
        
        .btn-unban {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            color: white;
        }
        
        .search-box {
            border-radius: 10px;
            border: 1px solid #dee2e6;
            padding: 10px 15px;
        }
        
        .search-box:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
        
        .badge-banned {
            background: #dc3545;
            padding: 5px 10px;
            border-radius: 8px;
        }
        
        .badge-active {
            background: #28a745;
            padding: 5px 10px;
            border-radius: 8px;
        }
        
        .modal-content {
            border-radius: 15px;
            border: none;
        }
        
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .filter-select {
            border-radius: 10px;
            border: 1px solid #dee2e6;
            padding: 10px;
        }
        
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
        
        .action-btn {
            padding: 5px 10px;
            margin: 0 3px;
            border-radius: 8px;
            font-size: 12px;
        }
    </style>
</head>

<body>

<!-- Navbar -->
<nav class="navbar navbar-custom mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/">
            <i class="fas fa-gavel text-danger me-2"></i>
            Banhammer System
        </a>
        <div class="d-flex gap-2">
            <a href="{{ route('history') }}" class="btn btn-outline-primary">
                <i class="fas fa-history"></i> History
            </a>
            <button class="btn btn-outline-secondary" onclick="refreshStats()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>
</nav>

<div class="container py-4">

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

   

    <!-- Search and Filters -->
    <div class="table-container mb-4">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control search-box" 
                           placeholder="Search by name or email..." value="{{ request('search') }}">
                </div>
            </div>
            
            <div class="col-md-3">
                <select name="status" class="form-select filter-select">
                    <option value="">All Users</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active Users</option>
                    <option value="banned" {{ request('status') == 'banned' ? 'selected' : '' }}>Banned Users</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
            
            <div class="col-md-3">
                <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#banMultipleModal">
                    <i class="fas fa-gavel"></i> Ban Multiple
                </button>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="table-container">
        <h5 class="mb-3 fw-bold">
            <i class="fas fa-table me-2"></i> User Management
        </h5>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Banned Since</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <input type="checkbox" class="user-checkbox form-check-input" value="{{ $user->id }}">
                            </td>
                            <td>{{ $user->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-2">
                                        <i class="fas fa-user text-primary"></i>
                                    </div>
                                    {{ $user->name }}
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->isBanned())
                                    <span class="badge badge-banned">
                                        <i class="fas fa-ban"></i> Banned
                                    </span>
                                @else
                                    <span class="badge badge-active">
                                        <i class="fas fa-check-circle"></i> Active
                                    </span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $banInfo = $user->bannedAt();
                                @endphp
                                @if($banInfo && isset($banInfo['expired_at']))
                                    {{ \Carbon\Carbon::parse($banInfo['expired_at'])->format('d M Y') }}
                                    @if($banInfo['expired_at'] > now())
                                        <small class="text-muted d-block">
                                            Expires in {{ now()->diffInDays($banInfo['expired_at']) }} days
                                        </small>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if(!$user->isBanned())
                                    <button class="btn btn-ban btn-sm" onclick="showBanModal({{ $user->id }}, '{{ $user->name }}')">
                                        <i class="fas fa-gavel"></i> Ban
                                    </button>
                                    <button class="btn btn-outline-warning btn-sm" onclick="showWarningModal({{ $user->id }}, '{{ $user->name }}')">
                                        <i class="fas fa-exclamation-triangle"></i> Warn
                                    </button>
                                @else
                                    <a href="{{ route('unban.user', $user->id) }}" class="btn btn-unban btn-sm">
                                        <i class="fas fa-check-circle"></i> Unban
                                    </a>
                                    <button class="btn btn-outline-info btn-sm" onclick="showExtendModal({{ $user->id }})">
                                        <i class="fas fa-clock"></i> Extend
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted">No users found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{ $users->links() }}
    </div>

    <!-- IP Ban Section -->
    <div class="table-container mt-4">
        <h5 class="mb-3 fw-bold">
            <i class="fas fa-ip me-2"></i> IP Address Banning
        </h5>
        
        <form method="POST" action="{{ route('ban.ip') }}" class="row g-3">
            @csrf
            <div class="col-md-6">
                <input type="text" name="ip" class="form-control search-box" 
                       placeholder="Enter IP Address (e.g., 192.168.1.1)" required>
            </div>
            <div class="col-md-4">
                <input type="text" name="reason" class="form-control search-box" 
                       placeholder="Reason for IP ban" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-danger w-100">
                    <i class="fas fa-ban"></i> Ban IP
                </button>
            </div>
        </form>
        
        @if(isset($bannedIPs) && $bannedIPs->count() > 0)
            <div class="mt-3">
                <small class="text-muted">Recently Banned IPs:</small>
                <div class="mt-2">
                    @foreach($bannedIPs as $ip)
                        <span class="badge bg-secondary me-2">
                            {{ $ip->ip }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Ban User Modal -->
<div class="modal fade" id="banModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ban User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="banForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Banning user: <strong id="banUserName"></strong></p>
                    
                    <div class="mb-3">
                        <label class="form-label">Reason for Ban</label>
                        <textarea name="reason" class="form-control" rows="3" required 
                                  placeholder="Enter reason for banning this user..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Duration</label>
                        <select name="duration" class="form-select" required>
                            <option value="1">1 Day</option>
                            <option value="3">3 Days</option>
                            <option value="7">7 Days</option>
                            <option value="30">30 Days</option>
                            <option value="permanent">Permanent</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Ban User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Warning Modal -->
<div class="modal fade" id="warningModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Warning</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="warningForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Sending warning to: <strong id="warningUserName"></strong></p>
                    <div class="mb-3">
                        <label class="form-label">Warning Message</label>
                        <textarea name="message" class="form-control" rows="3" required 
                                  placeholder="Enter warning message..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Send Warning</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Ban Multiple Modal -->
<div class="modal fade" id="banMultipleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ban Multiple Users</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('ban.multiple') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Reason for Ban</label>
                        <textarea name="reason" class="form-control" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Duration</label>
                        <select name="duration" class="form-select" required>
                            <option value="1">1 Day</option>
                            <option value="3">3 Days</option>
                            <option value="7">7 Days</option>
                            <option value="30">30 Days</option>
                            <option value="permanent">Permanent</option>
                        </select>
                    </div>
                    
                    <div id="selectedUsers"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Ban Selected</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Show Ban Modal
    function showBanModal(userId, userName) {
        const form = document.getElementById('banForm');
        form.action = '/ban/' + userId;
        document.getElementById('banUserName').textContent = userName;
        new bootstrap.Modal(document.getElementById('banModal')).show();
    }
    
    // Show Warning Modal
    function showWarningModal(userId, userName) {
        const form = document.getElementById('warningForm');
        form.action = '/send-notification/' + userId;
        document.getElementById('warningUserName').textContent = userName;
        new bootstrap.Modal(document.getElementById('warningModal')).show();
    }
    
    // Select All Checkboxes
    document.getElementById('selectAll')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.user-checkbox');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        updateSelectedCount();
    });
    
    // Update Selected Users Count
    function updateSelectedCount() {
        const selected = document.querySelectorAll('.user-checkbox:checked').length;
        const selectedUsersDiv = document.getElementById('selectedUsers');
        if (selectedUsersDiv) {
            selectedUsersDiv.innerHTML = `<p class="text-muted">Selected users: <strong>${selected}</strong></p>`;
            
            // Add hidden inputs for selected users
            const hiddenInputs = document.querySelectorAll('input[name="user_ids[]"]');
            hiddenInputs.forEach(input => input.remove());
            
            document.querySelectorAll('.user-checkbox:checked').forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'user_ids[]';
                input.value = checkbox.value;
                selectedUsersDiv.appendChild(input);
            });
        }
    }
    
    // Refresh Stats
    function refreshStats() {
        location.reload();
    }
    
    // Auto-refresh every 30 seconds (optional)
    // setInterval(refreshStats, 30000);
    
    // Initialize checkbox listeners
    document.querySelectorAll('.user-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });
</script>

</body>
</html>