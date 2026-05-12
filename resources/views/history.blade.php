<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Ban History</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background:#0f172a; color:white;">

<div class="container py-5">

    <div class="d-flex justify-content-between mb-4">
        <h1>📜 Ban History</h1>

        <a href="/" class="btn btn-primary">
            Dashboard
        </a>
    </div>

    <table class="table table-dark table-bordered">

        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Email</th>
                <th>IP</th>
                <th>Reason</th>
                <th>Expiry</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>

        <tbody>

        @foreach($logs as $log)

            <tr>

                <td>{{ $log->id }}</td>

                <td>{{ $log->user_name ?? '-' }}</td>

                <td>{{ $log->email ?? '-' }}</td>

                <td>{{ $log->ip ?? '-' }}</td>

                <td>{{ $log->reason }}</td>

                <td>{{ $log->expired_at ?? '-' }}</td>

                <td>
                    @if($log->status == 'banned')
                        <span class="badge bg-danger">
                            Banned
                        </span>
                    @else
                        <span class="badge bg-success">
                            Unbanned
                        </span>
                    @endif
                </td>

                <td>{{ $log->created_at->format('d M Y h:i A') }}</td>

            </tr>

        @endforeach

        </tbody>

    </table>

</div>

</body>
</html>