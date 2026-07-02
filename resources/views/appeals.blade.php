<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Appeals - Banhammer System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body style="background:#f0f2f5;font-family:'Inter',sans-serif;">

<nav class="navbar navbar-custom mb-4" style="background:white;box-shadow:0 2px 10px rgba(0,0,0,0.1);">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/">
            <i class="fas fa-gavel text-danger me-2"></i>
            Banhammer System
        </a>
        <div class="d-flex gap-2">
            <a href="/" class="btn btn-primary btn-sm">
                <i class="fas fa-dashboard"></i> Dashboard
            </a>
            <a href="{{ route('history') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-history"></i> History
            </a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <h2 class="fw-bold mb-4">
        <i class="fas fa-gavel me-2 text-warning"></i>
        Appeal Management
        <span class="badge bg-warning text-dark ms-2">{{ $stats['pending'] ?? 0 }} Pending</span>
    </h2>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Total Appeals</h6>
                    <h3 class="fw-bold">{{ $stats['total'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Pending</h6>
                    <h3 class="fw-bold text-warning">{{ $stats['pending'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Approved</h6>
                    <h3 class="fw-bold text-success">{{ $stats['approved'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Rejected</h6>
                    <h3 class="fw-bold text-danger">{{ $stats['rejected'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Ban Reason</th>
                            <th>Appeal Message</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appeals as $appeal)
                        <tr>
                            <td>{{ $appeal->id }}</td>
                            <td>
                                <strong>{{ $appeal->user->name ?? 'Unknown' }}</strong><br>
                                <small class="text-muted">{{ $appeal->user->email ?? '' }}</small>
                            </td>
                            <td>
                                <span title="{{ $appeal->banLog->reason ?? '' }}">
                                    {{ Str::limit($appeal->banLog->reason ?? 'N/A', 40) }}
                                </span>
                            </td>
                            <td>
                                <span title="{{ $appeal->message }}">
                                    {{ Str::limit($appeal->message, 50) }}
                                </span>
                            </td>
                            <td>
                                @if($appeal->status == 'pending')
                                    <span class="badge bg-warning text-dark">⏳ Pending</span>
                                @elseif($appeal->status == 'approved')
                                    <span class="badge bg-success">✅ Approved</span>
                                @else
                                    <span class="badge bg-danger">❌ Rejected</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $appeal->created_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                @if($appeal->status == 'pending')
                                    <form action="{{ route('appeal.review', $appeal->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('appeal.review', $appeal->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted">Reviewed</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No appeals found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $appeals->links() }}
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>