<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Banhammer Dashboard</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body{
            background:#0f172a;
            color:white;
            font-family:Arial;
        }

        .card-box{
            border-radius:20px;
            padding:25px;
            color:white;
            box-shadow:0 10px 30px rgba(0,0,0,0.3);
        }

        .table{
            border-radius:15px;
            overflow:hidden;
        }

        .table thead{
            background:#1e293b;
            color:white;
        }

        .table tbody tr{
            background:#1e293b;
            color:white;
        }

        .btn-ban{
            background:#ef4444;
            color:white;
        }

        .btn-unban{
            background:#10b981;
            color:white;
        }

        .search-box{
            background:#1e293b;
            border:none;
            color:white;
        }

        .search-box:focus{
            background:#1e293b;
            color:white;
            box-shadow:none;
        }
    </style>
</head>

<body>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>🛡️ Banhammer Dashboard</h1>

        <a href="/ban-history" class="btn btn-warning">
            Ban History
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Statistics -->

    <div class="row mb-4">

        <div class="col-md-4">
            <div class="card-box bg-primary">
                <h3>{{ $totalUsers }}</h3>
                <p>Total Users</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card-box bg-danger">
                <h3>{{ $bannedUsers }}</h3>
                <p>Banned Users</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card-box bg-success">
                <h3>{{ $activeUsers }}</h3>
                <p>Active Users</p>
            </div>
        </div>

    </div>

    <!-- Search -->

    <form method="GET" class="row mb-4">

        <div class="col-md-5">
            <input type="text"
                   name="search"
                   class="form-control search-box"
                   placeholder="Search user..."
                   value="{{ request('search') }}">
        </div>

        <div class="col-md-3">
            <select name="status" class="form-select search-box">
                <option value="">All Users</option>
                <option value="active">Active Users</option>
                <option value="banned">Banned Users</option>
            </select>
        </div>

        <div class="col-md-2">
            <button class="btn btn-info w-100">
                Search
            </button>
        </div>

    </form>

    <!-- User Table -->

    <div class="table-responsive">

        <table class="table align-middle">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>

            @foreach($users as $user)

                <tr>

                    <td>{{ $user->id }}</td>

                    <td>{{ $user->name }}</td>

                    <td>{{ $user->email }}</td>

                    <td>
                        @if($user->isBanned())
                            <span class="badge bg-danger">
                                Banned
                            </span>
                        @else
                            <span class="badge bg-success">
                                Active
                            </span>
                        @endif
                    </td>

                    <td>

                        @if(!$user->isBanned())

                        <form action="/ban/{{ $user->id }}" method="POST">
                            @csrf

                            <div class="d-flex gap-2">

                                <input type="text"
                                       name="reason"
                                       class="form-control"
                                       placeholder="Ban reason">

                                <button class="btn btn-ban">
                                    Ban
                                </button>

                            </div>
                        </form>

                        @else

                        <a href="/unban/{{ $user->id }}"
                           class="btn btn-unban">
                            Unban
                        </a>

                        @endif

                    </td>

                </tr>

            @endforeach

            </tbody>

        </table>

    </div>

    <!-- IP Ban -->

    <div class="card bg-dark p-4 mt-5">

        <h3 class="mb-3">🌐 Ban IP Address</h3>

        <form method="POST" action="/ban-ip">

            @csrf

            <div class="row">

                <div class="col-md-8">
                    <input type="text"
                           name="ip"
                           class="form-control"
                           placeholder="Enter IP Address">
                </div>

                <div class="col-md-4">
                    <button class="btn btn-danger w-100">
                        Ban IP
                    </button>
                </div>

            </div>

        </form>

    </div>

</div>

</body>
</html>