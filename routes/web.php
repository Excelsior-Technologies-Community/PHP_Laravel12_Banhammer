<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BanController;

Route::get('/', [BanController::class, 'index']);

Route::get('/ban/{id}', [BanController::class, 'banUser']);
Route::get('/unban/{id}', [BanController::class, 'unbanUser']);

Route::post('/ban-ip', [BanController::class, 'banIP']);