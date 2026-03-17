# PHP_Laravel12_Banhammer

## Project Description

PHP_Laravel12_Banhammer is a simple Laravel 12 web application that demonstrates how to implement a user banning system using the Banhammer package.

The application allows administrators to ban or unban users, ban IP addresses, and manage user access easily through a clean interface.

This project is designed for beginners to understand how real-world applications handle user restrictions, security, and moderation systems.


## Features

- Display all users in a table

- Ban users with one click

- Unban users easily

- Temporary ban support (with expiry time)

- Ban IP addresses

- Show user status (Active / Banned)

- Real-time UI updates after actions

- Store ban data in database



## How It Works

- Users are stored in the database

- Admin clicks Ban → user is marked as banned

- Ban details are saved in the bans table

- UI checks status using isBanned() method

- Admin can unban user anytime

- IP addresses can also be blocked



## Technologies Used

- PHP 8+

- Laravel 12

- MySQL

- Blade Template Engine

- Banhammer Package (mchev/banhammer)

- HTML + CSS (UI Design)



---



## Installation Steps


---


## STEP 1: Create Laravel 12 Project

### Open terminal / CMD and run:

```
composer create-project laravel/laravel PHP_Laravel12_Banhammer "12.*"

```

### Go inside project:

```
cd PHP_Laravel12_Banhammer

```

#### Explanation:

This command installs a fresh Laravel 12 project and creates a new folder named PHP_Laravel12_Banhammer.

The cd command moves into the project directory to start development.




## STEP 2: Database Setup 

### Update database details:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel12_Banhammer
DB_USERNAME=root
DB_PASSWORD=

```

### Create database in MySQL / phpMyAdmin:

```
Database name: laravel12_Banhammer

```

### Then Run:

```
php artisan migrate

```


#### Explanation:

This step connects Laravel to MySQL using the .env file.

Running migration creates default tables like users in the database.





## STEP 3: Install Banhammer Package

### Install package:

```
composer require mchev/banhammer

```

### Publish Files

```
php artisan vendor:publish --provider="Mchev\Banhammer\BanhammerServiceProvider" --tag="migrations"
php artisan vendor:publish --provider="Mchev\Banhammer\BanhammerServiceProvider" --tag="config"

```

### Then Run:

```
php artisan migrate

```



#### Explanation:

This installs the Banhammer package, which provides ban functionality for users and IPs.

Publishing creates migration and config files, and migration creates the bans table.






## STEP 4: Make User Model Bannable

### Open: app/Models/User.php

#### Add:

```
use Mchev\Banhammer\Traits\Bannable;

class User extends Authenticatable
{
    use Bannable;
}

```

#### Explanation:

The Bannable trait adds ban-related methods like ban(), unban(), and isBanned() to the User model.





## STEP 5: Create Controller

### Run:

```
php artisan make:controller BanController

```

### Open: app/Http/Controllers/BanController.php

```
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Mchev\Banhammer\IP;

class BanController extends Controller
{
    // Show users
    public function index()
    {
        $users = User::all();
        return view('welcome', compact('users'));
    }

    // Ban user
    public function banUser($id)
    {
        $user = User::findOrFail($id);

        $user->ban([
            'comment' => 'You are banned by admin',
            'expired_at' => now()->addDays(2)
        ]);

        return back()->with('success', 'User banned');
    }

    // Unban user
    public function unbanUser($id)
    {
        $user = User::findOrFail($id);
        $user->unban();

        return back()->with('success', 'User unbanned');
    }

    // Ban IP
    public function banIP(Request $request)
    {
        IP::ban($request->ip);

        return back()->with('success', 'IP banned');
    }
}

```

#### Explanation:

This command creates a controller to manage ban/unban logic and handle user requests.



## STEP 6: Routes

### Open: routes/web.php

```
use App\Http\Controllers\BanController;

Route::get('/', [BanController::class, 'index']);

Route::get('/ban/{id}', [BanController::class, 'banUser']);
Route::get('/unban/{id}', [BanController::class, 'unbanUser']);

Route::post('/ban-ip', [BanController::class, 'banIP']);

```

#### Explanation:

Routes define URLs and connect them to controller functions for displaying users, banning, unbanning, and banning IPs.




## STEP 7: Blade UI

### Open: resources/views/welcome.blade.php

```
<!DOCTYPE html>
<html>
<head>
    <title>Banhammer</title>
</head>
<body>

<h2>User List</h2>

@if(session('success'))
    <p style="color:green">{{ session('success') }}</p>
@endif

<table border="1" cellpadding="10">
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
                <span style="color:red">Banned</span>
            @else
                <span style="color:green">Active</span>
            @endif
        </td>
        <td>
            @if(!$user->isBanned())
                <a href="/ban/{{ $user->id }}">Ban</a>
            @else
                <a href="/unban/{{ $user->id }}">Unban</a>
            @endif
        </td>
    </tr>
    @endforeach
</table>

<hr>

<h3>Ban IP</h3>
<form method="POST" action="/ban-ip">
    @csrf
    <input type="text" name="ip" placeholder="Enter IP">
    <button type="submit">Ban IP</button>
</form>

</body>
</html>

```

#### Explanation:

This view displays all users in a table with their status and provides buttons to ban/unban users and a form to ban IP addresses




## STEP 8: Add Dummy Users

### Run this command:

```
php artisan tinker

```

### Then add users:

```
\App\Models\User::create([
    'name' => 'Demo User 1',
    'email' => 'demo1@gmail.com',
    'password' => bcrypt('123456')
]);

\App\Models\User::create([
    'name' => 'Demo User 2',
    'email' => 'demo2@gmail.com',
    'password' => bcrypt('123456')
]);

```

### Exit:

```
exit

```

#### Explanation:

Tinker is used to quickly insert test users into the database so the UI can display and test ban functionality.




## STEP 9: Run the App

### Start dev server:

```
php artisan serve

```

### Open in browser:

```
http://127.0.0.1:8000

```

#### Explanation:

This starts the Laravel development server and allows you to access the application in the browser.




## Expected Output:


### Main Page:


<img src="screenshots/Screenshot 2026-03-17 121314.png" width="900">


### Ban Ip Address:


<img src="screenshots/Screenshot 2026-03-17 121335.png" width="900">


### User Banned:


<img src="screenshots/Screenshot 2026-03-17 121406.png" width="900">



### User UnBanned:


<img src="screenshots/Screenshot 2026-03-17 121720.png" width="900">



---

## Project Folder Structure:

```
PHP_Laravel12_Banhammer/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── BanController.php
│   │
│   ├── Models/
│   │   └── User.php   (Bannable trait added)
│
├── bootstrap/
│
├── config/
│   └── banhammer.php   (published config file)
│
├── database/
│   ├── migrations/
│   │   ├── 2024_xx_xx_create_users_table.php
│   │   ├── 2024_xx_xx_create_bans_table.php
│   │
│   └── seeders/
│
├── public/
│
├── resources/
│   ├── views/
│   │   └── welcome.blade.php
│
├── routes/
│   └── web.php
│
├── storage/
│
├── tests/
│
├── vendor/
│
├── .env
├── artisan
├── composer.json
└── README.md

```
