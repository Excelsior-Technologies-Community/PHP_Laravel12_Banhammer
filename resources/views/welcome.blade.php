<!DOCTYPE html>
<html>

<head>
    <title>Banhammer</title>

    <style>
        body {
            background-color: #0f172a;
            color: #e2e8f0;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        h2,
        h3 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #1e293b;
            border-radius: 10px;
            overflow: hidden;
        }

        th {
            background-color: #334155;
            padding: 12px;
        }

        td {
            padding: 10px;
            text-align: center;
        }

        tr:nth-child(even) {
            background-color: #1e293b;
        }

        tr:nth-child(odd) {
            background-color: #0f172a;
        }

        .active {
            color: #22c55e;
            font-weight: bold;
        }

        .banned {
            color: #ef4444;
            font-weight: bold;
        }

        a {
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 5px;
            color: white;
        }

        .ban-btn {
            background-color: #ef4444;
        }

        .unban-btn {
            background-color: #22c55e;
        }

        a:hover {
            opacity: 0.8;
        }

        .success {
            text-align: center;
            color: #22c55e;
            margin-top: 10px;
        }

        hr {
            margin: 40px 0;
            border: 1px solid #334155;
        }

        form {
            text-align: center;
        }

        input {
            padding: 10px;
            border-radius: 5px;
            border: none;
            outline: none;
            width: 200px;
        }

        button {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            background-color: #6366f1;
            color: white;
            cursor: pointer;
            margin-left: 10px;
        }

        button:hover {
            opacity: 0.8;
        }
    </style>

</head>

<body>

    <h2>🚫 Banhammer User Panel</h2>

    @if(session('success'))
        <p class="success">{{ session('success') }}</p>
    @endif

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        @foreach($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>

                <td>
                    @if($user->isBanned())
                        <span class="banned">Banned</span>
                    @else
                        <span class="active">Active</span>
                    @endif
                </td>

                <td>
                    @if(!$user->isBanned())
                        <a href="/ban/{{ $user->id }}" class="ban-btn">Ban</a>
                    @else
                        <a href="/unban/{{ $user->id }}" class="unban-btn">Unban</a>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>

    <hr>

    <h3>🌐 Ban IP Address</h3>

    <form method="POST" action="/ban-ip">
        @csrf
        <input type="text" name="ip" placeholder="Enter IP (e.g. 127.0.0.1)">
        <button type="submit">Ban IP</button>
    </form>

</body>

</html>